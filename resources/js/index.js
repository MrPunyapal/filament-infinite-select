/**
 * Infinite scroll detection for Filament Select component
 * This is a minimal enhancement that hooks into Filament's existing Select
 * and adds pagination support via Livewire calls
 */
export default function infiniteScrollSelect({
    getPaginatedOptionsUsing,
    perPage,
    loadingMessage = 'Loading...',
}) {
    return {
        offset: 0,
        hasMore: true,
        isLoadingMore: false,
        currentSearch: null,
        loaderEl: null,

        init() {
            // Wait for the dropdown to be rendered
            this.$nextTick(() => {
                this.attachScrollListener()
                this.attachSearchListener()
            })
        },

        attachScrollListener() {
            // Find the dropdown list element rendered by Filament's Select
            const container = this.$el

            // Watch for dropdown open and attach scroll listener
            const observer = new MutationObserver(() => {
                const dropdown = container.querySelector('.fi-dropdown-panel')
                if (dropdown && !dropdown.hasAttribute('data-scroll-attached')) {
                    dropdown.setAttribute('data-scroll-attached', 'true')
                    dropdown.addEventListener('scroll', () => this.handleScroll(dropdown))

                    // Load initial page when dropdown opens
                    if (this.offset === 0 && this.hasMore) {
                        this.loadMoreOptions()
                    }
                }
            })

            observer.observe(container, { childList: true, subtree: true })
        },

        attachSearchListener() {
            const container = this.$el

            // Watch for search input changes
            const observer = new MutationObserver(() => {
                const searchInput = container.querySelector('.fi-select-input-search-ctn input')
                if (searchInput && !searchInput.hasAttribute('data-search-attached')) {
                    searchInput.setAttribute('data-search-attached', 'true')
                    searchInput.addEventListener('input', (e) => {
                        this.currentSearch = e.target.value
                        this.offset = 0
                        this.hasMore = true
                    })
                }
            })

            observer.observe(container, { childList: true, subtree: true })
        },

        handleScroll(dropdown) {
            if (this.isLoadingMore || !this.hasMore) return

            const threshold = 50
            const scrollBottom = dropdown.scrollHeight - dropdown.scrollTop - dropdown.clientHeight

            if (scrollBottom < threshold) {
                this.loadMoreOptions()
            }
        },

        showLoader() {
            const dropdown = this.$el.querySelector('.fi-dropdown-panel ul')
            if (!dropdown || this.loaderEl) return

            this.loaderEl = document.createElement('li')
            this.loaderEl.className = 'fi-dropdown-list-item fi-select-input-loading'
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

        hideLoader() {
            if (this.loaderEl) {
                this.loaderEl.remove()
                this.loaderEl = null
            }
        },

        async loadMoreOptions() {
            if (this.isLoadingMore || !this.hasMore) return

            this.isLoadingMore = true
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
            } finally {
                this.isLoadingMore = false
            }
        },

        appendOptionsToDropdown(options) {
            const dropdown = this.$el.querySelector('.fi-dropdown-panel ul')
            if (!dropdown) return

            options.forEach(option => {
                const li = document.createElement('li')
                li.className = 'fi-dropdown-list-item fi-select-input-option'
                li.setAttribute('role', 'option')
                li.setAttribute('data-value', option.value)
                li.setAttribute('tabindex', '0')

                const span = document.createElement('span')
                span.textContent = option.label
                li.appendChild(span)

                li.addEventListener('click', (e) => {
                    e.preventDefault()
                    e.stopPropagation()
                    // Dispatch custom event that Filament's Select can handle
                    this.$dispatch('select-option', { value: option.value, label: option.label })
                })

                dropdown.appendChild(li)
            })
        },
    }
}
