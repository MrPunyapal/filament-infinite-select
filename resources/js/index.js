export default function infiniteSelectFormComponent({
    canOptionLabelsWrap,
    canSelectPlaceholder,
    getOptionLabelUsing,
    getOptionLabelsUsing,
    getOptionsUsing,
    getSearchResultsUsing,
    getPaginatedOptionsUsing,
    hasDynamicOptions,
    hasDynamicSearchResults,
    hasInitialNoOptionsMessage,
    hasInfiniteScroll,
    initialOptionLabel,
    initialOptionLabels,
    initialState,
    isAutofocused,
    isDisabled,
    isHtmlAllowed,
    isMultiple,
    isReorderable,
    isSearchable,
    livewireId,
    loadingMessage,
    maxItems,
    maxItemsMessage,
    noOptionsMessage,
    noSearchResultsMessage,
    options,
    optionsLimit,
    perPage,
    placeholder,
    position,
    searchDebounce,
    searchingMessage,
    searchPrompt,
    searchableOptionFields,
    state,
    statePath,
}) {
    return {
        select: null,
        selectedOptions: [],
        isLoading: false,
        isLoadingMore: false,
        offset: 0,
        hasMore: true,
        currentSearch: null,
        observer: null,
        sentinelEl: null,

        init() {
            this.initSelect()
            this.setupInfiniteScroll()
        },

        initSelect() {
            this.select = window.Filament.createSelect(this.$refs.select, {
                allowHtml: isHtmlAllowed,
                allowWrap: canOptionLabelsWrap,
                autofocus: isAutofocused,
                disabled: isDisabled,
                items: options,
                loadingMessage,
                maxItemMessage: maxItemsMessage,
                maxItems: maxItems ?? (isMultiple ? null : 1),
                noOptionsMessage: hasInitialNoOptionsMessage ? noOptionsMessage : null,
                noSearchResultsMessage,
                placeholder: canSelectPlaceholder ? placeholder : null,
                position,
                searchable: isSearchable,
                searchableFields: searchableOptionFields ?? ['label'],
                searchDebounce,
                searchMessage: searchingMessage,
                searchPlaceholder: searchPrompt,
                sortable: isReorderable,

                onDropdownOpen: async () => {
                    if (hasInfiniteScroll && this.offset === 0 && this.hasMore) {
                        await this.loadMoreOptions()
                    }
                },

                onSearch: async (search) => {
                    this.currentSearch = search

                    if (hasInfiniteScroll) {
                        this.resetPagination()
                        await this.loadMoreOptions()
                        return
                    }

                    if (!hasDynamicSearchResults) {
                        return
                    }

                    this.isLoading = true
                    this.select.setOptions(await getSearchResultsUsing(search))
                    this.isLoading = false
                },

                onChange: (value) => {
                    state = isMultiple
                        ? value.map((option) => option.value)
                        : value?.value ?? null

                    this.selectedOptions = isMultiple
                        ? value
                        : (value ? [value] : [])
                },
            })

            this.setInitialState()
        },

        setInitialState() {
            if (isMultiple) {
                let labels = initialOptionLabels ?? []
                let values = initialState ?? []

                this.selectedOptions = values.map((value, index) => ({
                    value: String(value),
                    label: labels[index]?.label ?? String(value),
                }))
            } else if (initialState !== null && initialState !== undefined) {
                this.selectedOptions = [{
                    value: String(initialState),
                    label: initialOptionLabel ?? String(initialState),
                }]
            }

            this.selectedOptions.forEach((option) => {
                this.select.selectOption(option)
            })
        },

        setupInfiniteScroll() {
            if (!hasInfiniteScroll) {
                return
            }

            this.$watch('select', () => {
                this.$nextTick(() => {
                    const dropdown = this.$refs.select.querySelector('.choices__list--dropdown .choices__list')
                    if (!dropdown) {
                        return
                    }

                    dropdown.addEventListener('scroll', () => {
                        this.handleScroll(dropdown)
                    })
                })
            })
        },

        handleScroll(container) {
            if (this.isLoadingMore || !this.hasMore) {
                return
            }

            const threshold = 50
            const scrollBottom = container.scrollHeight - container.scrollTop - container.clientHeight

            if (scrollBottom < threshold) {
                this.loadMoreOptions()
            }
        },

        resetPagination() {
            this.offset = 0
            this.hasMore = true
            this.select.clearOptions()

            this.selectedOptions.forEach((option) => {
                this.select.selectOption(option)
            })
        },

        async loadMoreOptions() {
            if (this.isLoadingMore || !this.hasMore) {
                return
            }

            this.isLoadingMore = true

            try {
                const result = await getPaginatedOptionsUsing(this.offset, this.currentSearch)
                const newOptions = result.options ?? []
                this.hasMore = result.hasMore ?? false

                newOptions.forEach((option) => {
                    this.select.appendOption(option)
                })

                this.offset += perPage
            } catch (error) {
                console.error('Failed to load options:', error)
            } finally {
                this.isLoadingMore = false
            }
        },

        enable() {
            this.select?.enable()
        },

        disable() {
            this.select?.disable()
        },

        refreshSelectedOptionLabel: async function () {
            if (isMultiple) {
                const labels = await getOptionLabelsUsing()
                this.selectedOptions = labels ?? []
                return
            }

            const label = await getOptionLabelUsing()
            if (label && this.selectedOptions.length) {
                this.selectedOptions[0].label = label
            }
        },

        destroy() {
            this.select?.destroy()
            this.observer?.disconnect()
        },
    }
}
