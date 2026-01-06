@php
    $fieldWrapperView = $getFieldWrapperView();
    $extraInputAttributeBag = $getExtraInputAttributeBag();
    $canSelectPlaceholder = $canSelectPlaceholder();
    $isAutofocused = $isAutofocused();
    $isDisabled = $isDisabled();
    $isMultiple = $isMultiple();
    $isReorderable = $isReorderable();
    $isSearchable = $isSearchable();
    $hasInitialNoOptionsMessage = $hasInitialNoOptionsMessage();
    $canOptionLabelsWrap = $canOptionLabelsWrap();
    $isRequired = $isRequired();
    $isConcealed = $isConcealed();
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
        :x-on:focus-input.stop="'\$el.querySelector(\'.fi-select-input-btn\')?.focus()'"
        :attributes="
            \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
                ->class([
                    'fi-fo-select',
                    'fi-fo-select-has-inline-prefix' => $isPrefixInline && (count($prefixActions) || $prefixIcon || filled($prefixLabel)),
                ])
        "
    >
        <div
            x-ignore
            ax-load
            ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('infinite-select', 'mrpunyapal/filament-select-with-lazy-loading') }}"
            x-data="infiniteSelectFormComponent({
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
                    return await $wire.callSchemaComponentMethod(
                        @js($key),
                        'getOptionsForJs',
                    )
                },
                getSearchResultsUsing: async (search) => {
                    return await $wire.callSchemaComponentMethod(
                        @js($key),
                        'getSearchResultsForJs',
                        { search },
                    )
                },
                getPaginatedOptionsUsing: async (offset, search) => {
                    return await $wire.callSchemaComponentMethod(
                        @js($key),
                        'getPaginatedOptionsForJs',
                        { offset, search },
                    )
                },
                hasDynamicOptions: @js($hasDynamicOptions()),
                hasDynamicSearchResults: @js($hasDynamicSearchResults()),
                hasInitialNoOptionsMessage: @js($hasInitialNoOptionsMessage),
                hasInfiniteScroll: @js($hasInfiniteScroll),
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
                options: @js($hasInfiniteScroll ? [] : $getOptionsForJs()),
                optionsLimit: @js($getOptionsLimit()),
                perPage: @js($perPage),
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
                ])), 0, 64)
            }}"
            x-on:keydown.esc="select.dropdown.isActive && $event.stopPropagation()"
            {{
                $attributes
                    ->merge($getExtraAlpineAttributes(), escape: false)
                    ->class(['fi-select-input'])
            }}
        >
            <div x-ref="select"></div>
        </div>
    </x-filament::input.wrapper>
</x-dynamic-component>
