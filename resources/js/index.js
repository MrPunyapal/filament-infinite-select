/**
 * Infinite scroll enhancement for Filament's Select form component.
 *
 * Hooks into Filament's existing `selectFormComponent` and adds
 * offset-based pagination via Livewire renderless method calls.
 * New options are appended to the dropdown as the user scrolls,
 * without re-rendering the whole select.
 *
 * @param {Object} config - Configuration object
 * @param {Function} config.getPaginatedOptionsUsing - Callback to fetch paginated options
 * @param {number} config.perPage - Number of items to load per page
 * @param {number} config.searchDebounce - Debounce delay for search in milliseconds
 * @param {number} config.scrollThreshold - Scroll threshold in pixels to trigger loading
 * @param {string} config.loadingMessage - Message to display while loading
 * @param {boolean} config.preloaded - Whether first page was preloaded server-side
 * @param {boolean} config.preloadedHasMore - Whether more results exist after preload
 * @returns {Object} Alpine.js component
 */
export default function infiniteScrollSelect({
    getPaginatedOptionsUsing,
    perPage,
    searchDebounce = 300,
    scrollThreshold = 50,
    loadingMessage = 'Loading...',
    preloaded = false,
    preloadedHasMore = false,
}) {
    // CSS class constants for maintainability
    const CSS_CLASSES = {
        LOADING: 'fi-select-input-loading',
        DROPDOWN_LIST_ITEM: 'fi-dropdown-list-item',
        OPTION_ROLE: 'option',
    }

    return {
        offset: preloaded ? perPage : 0,
        hasMore: preloaded ? preloadedHasMore : true,
        isLoadingMore: false,
        currentSearch: null,
        loaderEl: null,
        errorEl: null,
        scrollObserver: null,
        searchObserver: null,
        preloaded: preloaded,
        _loadedOptions: [], // Non-reactive: Track all loaded options
        savedScrollPosition: 0, // Track scroll position before selection

        /**
         * Initialize the component and attach listeners
         */
        init() {
            this.$nextTick(() => {
                this.attachScrollListener()
                this.attachSearchListener()
                this.attachDropdownReopenListener()
            })
        },

        /**
         * Cleanup method to disconnect observers and prevent memory leaks
         */
        destroy() {
            if (this.scrollObserver) {
                this.scrollObserver.disconnect()
                this.scrollObserver = null
            }
            if (this.searchObserver) {
                this.searchObserver.disconnect()
                this.searchObserver = null
            }
        },

        /**
         * Attach scroll event listener to the dropdown
         */
        attachScrollListener() {
            const container = this.$el

            // Watch for dropdown open and attach scroll listener
            this.scrollObserver = new MutationObserver(() => {
                const dropdown = container.querySelector('.fi-dropdown-panel')
                if (dropdown && !dropdown.hasAttribute('data-scroll-attached')) {
                    dropdown.setAttribute('data-scroll-attached', 'true')
                    dropdown.addEventListener('scroll', () => this.handleScroll(dropdown), { passive: true })

                    // Load initial page when dropdown opens (skip if preloaded)
                    if (!this.preloaded && this.offset === 0 && this.hasMore) {
                        this.loadMoreOptions()
                    }
                }
            })

            this.scrollObserver.observe(container, { childList: true, subtree: true })
        },

        /**
         * Attach search input listener with debouncing
         */
        attachSearchListener() {
            const container = this.$el

            // Watch for search input changes
            this.searchObserver = new MutationObserver(() => {
                const searchInput = container.querySelector('.fi-select-input-search-ctn input')
                if (searchInput && !searchInput.hasAttribute('data-search-attached')) {
                    searchInput.setAttribute('data-search-attached', 'true')

                    let searchTimeout
                    // Prevent Filament from processing the input by stopping propagation
                    searchInput.addEventListener('input', (e) => {
                        e.stopImmediatePropagation()

                        const newSearch = e.target.value

                        // Clear timeout if already running
                        clearTimeout(searchTimeout)

                        // Debounce the actual search with configurable delay
                        searchTimeout = setTimeout(() => {
                            // Only reload if search actually changed
                            if (this.currentSearch !== newSearch) {
                                this.currentSearch = newSearch
                                this.offset = 0
                                this.hasMore = true
                                this._loadedOptions = [] // Clear loaded options on new search

                                // Clear existing options
                                const dropdown = container.querySelector('.fi-dropdown-panel ul')
                                if (dropdown) {
                                    // Remove all option items (but keep loader)
                                    dropdown.querySelectorAll(`li[role="${CSS_CLASSES.OPTION_ROLE}"]`).forEach(el => {
                                        if (!el.classList.contains(CSS_CLASSES.LOADING)) {
                                            el.remove()
                                        }
                                    })
                                }
                                this.hideError()

                                // Load fresh results
                                this.loadMoreOptions()
                            }
                        }, searchDebounce)
                    }, true) // Use capture phase to intercept before Filament's handlers
                }
            })

            this.searchObserver.observe(container, { childList: true, subtree: true })
        },

        /**
         * Attach listener for dropdown reopen to restore loaded options
         */
        attachDropdownReopenListener() {
            const container = this.$el
            let restoreTimeout = null
            let isRestoring = false

            // Watch for dropdown mutations and restore options when needed
            const observer = new MutationObserver(() => {
                // Skip if we're currently restoring to prevent cascade
                if (isRestoring) return

                const dropdownPanel = container.querySelector('.fi-dropdown-panel')
                const dropdown = container.querySelector('.fi-dropdown-panel ul')
                if (dropdown && dropdownPanel && this._loadedOptions.length > 0) {
                    // Use minimal debounce for faster restoration
                    clearTimeout(restoreTimeout)
                    restoreTimeout = setTimeout(() => {
                        // Get current selected values first
                        const selectContainer = container.querySelector('[x-data*="selectFormComponent"]')
                        const selectComponent = selectContainer ? Alpine.$data(selectContainer) : null
                        const selectedValues = selectComponent && selectComponent.select ?
                            (Array.isArray(selectComponent.select.value) ? selectComponent.select.value.map(String) : [String(selectComponent.select.value)]) : []

                        // Check if our loaded options are missing from the dropdown
                        const dropdownValues = new Set(
                            Array.from(dropdown.querySelectorAll('li[data-value]'))
                                .map(li => li.getAttribute('data-value'))
                        )

                        // Filter out missing options, but exclude selected ones
                        const missingOptions = this._loadedOptions.filter(opt => {
                            const valueStr = String(opt.value)
                            return !dropdownValues.has(valueStr) && !selectedValues.includes(valueStr)
                        })

                        if (missingOptions.length > 0) {
                            // Set flag to prevent observer from triggering during restoration
                            isRestoring = true

                            // Use DocumentFragment for faster batch DOM insertion
                            const fragment = document.createDocumentFragment()

                            missingOptions.forEach(option => {
                                // Create element without appending to DOM yet
                                const li = document.createElement('li')
                                li.className = CSS_CLASSES.DROPDOWN_LIST_ITEM
                                li.setAttribute('role', CSS_CLASSES.OPTION_ROLE)
                                li.setAttribute('data-value', String(option.value))
                                li.style.cursor = 'pointer'
                                li.setAttribute('aria-selected', 'false')

                                const label = document.createElement('span')
                                label.textContent = option.label
                                label.className = 'fi-dropdown-list-item-label'

                                li.appendChild(label)

                                li.setAttribute('x-on:click', `
                                    const dropdown = $el.closest('.fi-dropdown-panel');
                                    savedScrollPosition = dropdown ? dropdown.scrollTop : 0;
                                    const optionValue = $el.getAttribute('data-value');

                                    // For multiple select, remove this option from DOM immediately
                                    if (select.isMultiple) {
                                        $el.remove();
                                    }

                                    select.selectOption(optionValue);
                                    if (select.isMultiple) {
                                        $event.stopPropagation();
                                        select.dropdown.isActive = true;
                                    }
                                `)

                                fragment.appendChild(li)
                            })

                            // Append all at once for single repaint
                            dropdown.appendChild(fragment)

                            // Restore scroll position immediately after append
                            dropdownPanel.scrollTop = this.savedScrollPosition

                            // Reset flag after a frame
                            requestAnimationFrame(() => {
                                isRestoring = false
                            })
                        }
                    }, 10)
                }
            })

            observer.observe(container, { childList: true, subtree: true })
        },

        /**
         * Handle scroll events to trigger loading more options
         * @param {HTMLElement} dropdown - The dropdown element
         */
        handleScroll(dropdown) {
            if (this.isLoadingMore || !this.hasMore) return

            const scrollBottom = dropdown.scrollHeight - dropdown.scrollTop - dropdown.clientHeight

            if (scrollBottom < scrollThreshold) {
                this.loadMoreOptions()
            }
        },

        /**
         * Show loading indicator in the dropdown
         */
        showLoader() {
            const dropdown = this.$el.querySelector('.fi-dropdown-panel ul')
            if (!dropdown || this.loaderEl) return

            this.loaderEl = document.createElement('li')
            this.loaderEl.className = `${CSS_CLASSES.DROPDOWN_LIST_ITEM} ${CSS_CLASSES.LOADING}`
            this.loaderEl.style.cssText = 'text-align: center; padding: 0.75rem; color: var(--gray-500); display: flex; align-items: center; justify-content: center; gap: 0.5rem;'

            // Add spinner SVG
            const spinner = document.createElement('span')
            spinner.innerHTML = '<svg class="animate-spin" style="width: 1rem; height: 1rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'

            const text = document.createElement('span')
            text.textContent = loadingMessage

            this.loaderEl.appendChild(spinner)
            this.loaderEl.appendChild(text)
            dropdown.appendChild(this.loaderEl)
        },

        /**
         * Hide loading indicator
         */
        hideLoader() {
            if (this.loaderEl) {
                this.loaderEl.remove()
                this.loaderEl = null
            }
        },

        /**
         * Show error message in the dropdown
         * @param {string} message - The error message to display
         */
        showError(message = 'Failed to load options. Please try again.') {
            const dropdown = this.$el.querySelector('.fi-dropdown-panel ul')
            if (!dropdown || this.errorEl) return

            this.errorEl = document.createElement('li')
            this.errorEl.className = `${CSS_CLASSES.DROPDOWN_LIST_ITEM} fi-select-input-error`
            this.errorEl.style.cssText = 'text-align: center; padding: 0.75rem; color: var(--danger-600); background-color: var(--danger-50); border-radius: 0.375rem; margin: 0.5rem;'

            const text = document.createElement('span')
            text.textContent = message

            this.errorEl.appendChild(text)
            dropdown.appendChild(this.errorEl)
        },

        /**
         * Hide error message
         */
        hideError() {
            if (this.errorEl) {
                this.errorEl.remove()
                this.errorEl = null
            }
        },

        /**
         * Load more options from the server
         */
        async loadMoreOptions() {
            if (this.isLoadingMore || !this.hasMore) return

            this.isLoadingMore = true
            this.hideError()
            this.showLoader()

            try {
                const result = await getPaginatedOptionsUsing(this.offset, this.currentSearch)
                const newOptions = result.options ?? []
                this.hasMore = result.hasMore ?? false

                this.hideLoader()

                // Append new options to the dropdown
                this.appendOptionsToDropdown(newOptions)

                this.offset += perPage
            } catch (error) {
                console.error('Failed to load options:', error)
                this.hideLoader()
                this.showError('Failed to load options. Please try again.')
            } finally {
                this.isLoadingMore = false
            }
        },

        /**
         * Append new options to the dropdown
         * @param {Array} options - Array of option objects with value and label
         */
        appendOptionsToDropdown(options) {
            const dropdown = this.$el.querySelector('.fi-dropdown-panel ul')
            if (!dropdown) {
                console.warn('InfiniteSelect: Dropdown list not found')
                return
            }

            // Get the Filament Select component instance
            const selectContainer = this.$el.querySelector('[x-data*="selectFormComponent"]')
            const selectComponent = selectContainer ? Alpine.$data(selectContainer) : null

            // Add each option
            options.forEach(option => {
                // Store in loadedOptions for restoration
                const exists = this._loadedOptions.some(opt => opt.value == option.value)
                if (!exists) {
                    this._loadedOptions.push(option)
                }

                // Add to dropdown DOM
                this.addOptionToDropdown(dropdown, option)

                // Also update the component's options array if available
                if (selectComponent && selectComponent.select && selectComponent.select.options) {
                    const existsInComponent = selectComponent.select.options.some(opt => opt.value == option.value)
                    if (!existsInComponent) {
                        selectComponent.select.options.push(option)
                    }
                }
            })
        },

        /**
         * Add a single option element to the dropdown
         * @param {HTMLElement} dropdown - The dropdown UL element
         * @param {Object} option - The option object with value and label
         */
        addOptionToDropdown(dropdown, option) {
            // Get current selected values
            const selectContainer = this.$el.querySelector('[x-data*="selectFormComponent"]')
            const selectComponent = selectContainer ? Alpine.$data(selectContainer) : null
            const selectedValues = selectComponent && selectComponent.select ?
                (Array.isArray(selectComponent.select.value) ? selectComponent.select.value : [selectComponent.select.value]) : []

            // Create the option element
            const li = document.createElement('li')
            li.className = CSS_CLASSES.DROPDOWN_LIST_ITEM
            li.setAttribute('role', CSS_CLASSES.OPTION_ROLE)
            li.setAttribute('data-value', String(option.value))
            li.style.cursor = 'pointer'

            // Check if this option is selected
            const isSelected = selectedValues.some(val => String(val) === String(option.value))
            if (isSelected) {
                li.classList.add('fi-active')
                li.setAttribute('aria-selected', 'true')
            } else {
                li.setAttribute('aria-selected', 'false')
            }

            const label = document.createElement('span')
            label.textContent = option.label
            label.className = 'fi-dropdown-list-item-label'

            li.appendChild(label)

            // Add click handler that saves scroll before selection
            li.setAttribute('x-on:click', `
                const dropdown = $el.closest('.fi-dropdown-panel');
                savedScrollPosition = dropdown ? dropdown.scrollTop : 0;
                const optionValue = $el.getAttribute('data-value');

                // For multiple select, remove this option from DOM immediately
                if (select.isMultiple) {
                    $el.remove();
                }

                select.selectOption(optionValue);
                if (select.isMultiple) {
                    $event.stopPropagation();
                    select.dropdown.isActive = true;
                }
            `)

            dropdown.appendChild(li)
        },
    }
}
