@php
    $fieldWrapperView = $getFieldWrapperView();
    $canSelectPlaceholder = $canSelectPlaceholder();
    $isAutofocused = $isAutofocused();
    $isDisabled = $isDisabled();
    $isMultiple = $isMultiple();
    $isReorderable = $isReorderable();
    $isSearchable = $isSearchable();
    $hasInitialNoOptionsMessage = $hasInitialNoOptionsMessage();
    $hasDynamicOptions = $hasDynamicOptions();
    $canOptionLabelsWrap = $canOptionLabelsWrap();
    $isHtmlAllowed = $isHtmlAllowed();
    $isPrefixInline = $isPrefixInline();
    $isSuffixInline = $isSuffixInline();
    $key = $getKey();
    $id = $getId();
    $prefixActions = $getPrefixActions();
    $prefixIcon = $getPrefixIcon();
    $prefixIconColor = $getPrefixIconColor();
    $prefixLabel = $getPrefixLabel();
    $suffixActions = $getSuffixActions();
    $suffixIcon = $getSuffixIcon();
    $suffixIconColor = $getSuffixIconColor();
    $suffixLabel = $getSuffixLabel();
    $statePath = $getStatePath();
    $state = $getState();
    $livewireKey = $getLivewireKey();

    $hasInfiniteScroll = $hasOptionsWithPagination();
    $perPage = $getPerPage();
    $searchDebounce = $getSearchDebounce();
    $scrollThreshold = $getScrollThreshold();
    $shouldPreload = $shouldPreloadFirstPage();
    $preloadedOptions = $shouldPreload && $hasInfiniteScroll ? $getPaginatedOptionsForJs(0, null) : [];
@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
    class="fi-fo-select-wrp"
>
    <x-filament::input.wrapper
        :disabled="$isDisabled"
        :inline-prefix="$isPrefixInline"
        :inline-suffix="$isSuffixInline"
        :prefix="$prefixLabel"
        :prefix-actions="$prefixActions"
        :prefix-icon="$prefixIcon"
        :prefix-icon-color="$prefixIconColor"
        :suffix="$suffixLabel"
        :suffix-actions="$suffixActions"
        :suffix-icon="$suffixIcon"
        :suffix-icon-color="$suffixIconColor"
        :valid="! $errors->has($statePath)"
        x-on:focus-input.stop="$el.querySelector('.fi-select-input-btn')?.focus()"
        :attributes="
            \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
                ->class([
                    'fi-fo-select',
                    'fi-fo-select-has-inline-prefix' => $isPrefixInline && (count($prefixActions) || $prefixIcon || filled($prefixLabel)),
                ])
        "
    >
        <div
            class="fi-hidden"
            x-data="{
                isDisabled: @js($isDisabled),
                init() {
                    const container = $el.nextElementSibling
                    container.dispatchEvent(
                        new CustomEvent('set-select-property', {
                            detail: { isDisabled: this.isDisabled },
                        }),
                    )
                },
            }"
        ></div>
        <div
            @if($hasInfiniteScroll)
                x-load
                x-load-src="{{ $getInfiniteScrollAlpineSrc() }}"
                x-data="infiniteScrollSelect({
                    getPaginatedOptionsUsing: async (offset, search) => {
                        return await $wire.callSchemaComponentMethod(@js($key), 'getPaginatedOptionsForJs', { offset, search })
                    },
                    perPage: @js($perPage),
                    searchDebounce: @js($searchDebounce),
                    scrollThreshold: @js($scrollThreshold),
                    loadingMessage: @js($getLoadingMessage()),
                    preloaded: @js($shouldPreload),
                    preloadedHasMore: @js($shouldPreload ? ($preloadedOptions['hasMore'] ?? false) : false),
                })"
            @endif
        >
            <div
                x-load
                x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('select', 'filament/forms') }}"
                x-data="selectFormComponent({
                            canOptionLabelsWrap: @js($canOptionLabelsWrap),
                            canSelectPlaceholder: @js($canSelectPlaceholder),
                            getOptionLabelUsing: async () => {
                                return await $wire.callSchemaComponentMethod(@js($key), 'getOptionLabel')
                            },
                            getOptionLabelsUsing: async () => {
                                return await $wire.callSchemaComponentMethod(
                                    @js($key),
                                    'getOptionLabelsForJs',
                                )
                            },
                            getOptionsUsing: async () => {
                                @if($hasInfiniteScroll)
                                    @if($shouldPreload)
                                        // Return preloaded options immediately
                                        return @js($preloadedOptions['options'] ?? [])
                                    @else
                                        const result = await $wire.callSchemaComponentMethod(
                                            @js($key),
                                            'getPaginatedOptionsForJs',
                                            { offset: 0, search: null }
                                        )
                                        return result?.options || []
                                    @endif
                                @else
                                    return await $wire.callSchemaComponentMethod(
                                        @js($key),
                                        'getOptionsForJs',
                                    )
                                @endif
                            },
                            getSearchResultsUsing: async (search) => {
                                @if($hasInfiniteScroll)
                                    // For infinite scroll, return null to prevent Filament from processing search
                                    // Our custom search listener handles this
                                    return null
                                @else
                                    return await $wire.callSchemaComponentMethod(
                                        @js($key),
                                        'getSearchResultsForJs',
                                        { search },
                                    )
                                @endif
                            },
                            hasDynamicOptions: @js($hasInfiniteScroll ? true : $hasDynamicOptions),
                            hasDynamicSearchResults: @js($hasInfiniteScroll ? false : $hasDynamicSearchResults()),
                            hasInitialNoOptionsMessage: @js($hasInitialNoOptionsMessage),
                            initialOptionLabel: @js((blank($state) || $isMultiple) ? null : $getOptionLabel()),
                            initialOptionLabels: @js((filled($state) && $isMultiple) ? $getOptionLabelsForJs() : []),
                            initialState: @js($state),
                            isAutofocused: @js($isAutofocused),
                            isDisabled: @js($isDisabled),
                            isHtmlAllowed: @js($isHtmlAllowed),
                            isMultiple: @js($isMultiple),
                            isReorderable: @js($isReorderable),
                            isSearchable: @js($isSearchable),
                            livewireId: @js($this->getId()),
                            loadingMessage: @js($getLoadingMessage()),
                            maxItems: @js($getMaxItems()),
                            maxItemsMessage: @js($getMaxItemsMessage()),
                            noOptionsMessage: @js($getNoOptionsMessage()),
                            noSearchResultsMessage: @js($getNoSearchResultsMessage()),
                            options: @js($shouldPreload && $hasInfiniteScroll ? ($preloadedOptions['options'] ?? []) : ($hasInfiniteScroll ? [] : $getOptionsForJs())),
                            optionsLimit: @js($getOptionsLimit()),
                            placeholder: @js($getPlaceholder()),
                            position: @js($getPosition()),
                            searchDebounce: @js($getSearchDebounce()),
                            searchingMessage: @js($getSearchingMessage()),
                            searchPrompt: @js($getSearchPrompt()),
                            searchableOptionFields: @js($getSearchableOptionFields()),
                            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
                            statePath: @js($statePath),
                        })"
                wire:ignore
                wire:key="{{ $livewireKey }}.{{
                    substr(md5(serialize([
                        $isDisabled,
                        $isReorderable,
                    ])), 0, 64)
                }}"
                x-on:keydown.esc="select.dropdown.isActive && $event.stopPropagation()"
                x-on:set-select-property="$event.detail.isDisabled ? select.disable() : select.enable()"
                {{
                    $attributes
                        ->merge($getExtraAlpineAttributes(), escape: false)
                        ->class(['fi-select-input'])
                }}
            >
                <div x-ref="select"></div>
            </div>
        </div>
    </x-filament::input.wrapper>
</x-dynamic-component>
