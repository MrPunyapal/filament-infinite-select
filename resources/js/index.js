import { Select } from './Select.js'

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
        state,
        isLoadingMore: false,
        offset: 0,
        hasMore: true,
        currentSearch: null,

        init() {
            this.select = new Select({
                canOptionLabelsWrap,
                canSelectPlaceholder,
                element: this.$refs.select,
                getOptionLabelUsing,
                getOptionLabelsUsing,
                getOptionsUsing: hasInfiniteScroll ? null : getOptionsUsing,
                getSearchResultsUsing: hasInfiniteScroll ? null : getSearchResultsUsing,
                hasDynamicOptions: hasInfiniteScroll ? false : hasDynamicOptions,
                hasDynamicSearchResults: hasInfiniteScroll ? false : hasDynamicSearchResults,
                hasInitialNoOptionsMessage,
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
                onStateChange: (newState) => {
                    this.state = newState
                },
                options,
                optionsLimit,
                placeholder,
                position,
                searchableOptionFields,
                searchDebounce,
                searchingMessage,
                searchPrompt,
                state: this.state,
                statePath,
            })

            this.$watch('state', (newState) => {
                if (this.select && this.select.state !== newState) {
                    this.select.state = newState
                    this.select.updateSelectedDisplay()
                    this.select.renderOptions()
                }
            })

            if (hasInfiniteScroll) {
                this.setupInfiniteScroll()
            }
        },

        setupInfiniteScroll() {
            // Override search handling for infinite scroll
            if (this.select.searchInput) {
                this.select.searchInput.addEventListener('input', async (event) => {
                    if (this.select.isDisabled) return

                    this.currentSearch = event.target.value
                    this.offset = 0
                    this.hasMore = true

                    // Clear current options
                    this.select.options = []
                    this.select.renderOptions()

                    // Load first page of results
                    await this.loadMoreOptions()
                })
            }

            // Add scroll listener to dropdown
            this.select.dropdown.addEventListener('scroll', () => {
                this.handleScroll()
            })

            // Load initial options when dropdown opens
            const originalOpen = this.select.openDropdown.bind(this.select)
            this.select.openDropdown = async () => {
                originalOpen()
                if (this.offset === 0 && this.hasMore) {
                    await this.loadMoreOptions()
                }
            }
        },

        handleScroll() {
            if (this.isLoadingMore || !this.hasMore) return

            const dropdown = this.select.dropdown
            const threshold = 50
            const scrollBottom = dropdown.scrollHeight - dropdown.scrollTop - dropdown.clientHeight

            if (scrollBottom < threshold) {
                this.loadMoreOptions()
            }
        },

        async loadMoreOptions() {
            if (this.isLoadingMore || !this.hasMore) return

            this.isLoadingMore = true

            try {
                const result = await getPaginatedOptionsUsing(this.offset, this.currentSearch)
                const newOptions = result.options ?? []
                this.hasMore = result.hasMore ?? false

                // Append new options
                this.select.options = [...this.select.options, ...newOptions]
                this.select.renderOptions()

                this.offset += perPage
            } catch (error) {
                console.error('Failed to load options:', error)
            } finally {
                this.isLoadingMore = false
            }
        },

        destroy() {
            if (this.select) {
                this.select.destroy()
                this.select = null
            }
        },
    }
}
