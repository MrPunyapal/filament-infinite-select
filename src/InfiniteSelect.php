<?php

namespace MrPunyapal\FilamentInfiniteSelect;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Contracts\Support\Arrayable;
use Livewire\Attributes\Renderless;

class InfiniteSelect extends Select
{
    protected ?Closure $getOptionsWithPaginationUsing = null;

    protected int | Closure $perPage = 15;

    protected function setUp(): void
    {
        parent::setUp();

        $this->native(false);
        $this->searchable();
        
        // Add infinite scroll Alpine component wrapper
        $this->registerListeners([
            'select-option' => [
                fn (InfiniteSelect $component, string $value) => $component->state(
                    $component->isMultiple()
                        ? array_merge($component->getState() ?? [], [$value])
                        : $value
                ),
            ],
        ]);
    }

    public function getOptionsWithPaginationUsing(?Closure $callback): static
    {
        $this->getOptionsWithPaginationUsing = $callback;

        return $this;
    }

    public function perPage(int | Closure $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function getPerPage(): int
    {
        return (int) $this->evaluate($this->perPage);
    }

    public function hasOptionsWithPagination(): bool
    {
        return $this->getOptionsWithPaginationUsing !== null;
    }

    public function getPaginatedOptions(int $offset, int $limit, ?string $search = null): array
    {
        if (! $this->getOptionsWithPaginationUsing) {
            return [
                'options' => [],
                'hasMore' => false,
            ];
        }

        $result = $this->evaluate($this->getOptionsWithPaginationUsing, [
            'offset' => $offset,
            'limit' => $limit,
            'search' => $search,
            'query' => $search,
        ]);

        if ($result instanceof Arrayable) {
            $result = $result->toArray();
        }

        $options = $result['options'] ?? $result;
        $hasMore = $result['hasMore'] ?? false;

        return [
            'options' => $options,
            'hasMore' => $hasMore,
        ];
    }

    #[ExposedLivewireMethod]
    #[Renderless]
    public function getPaginatedOptionsForJs(int $offset, ?string $search = null): array
    {
        $result = $this->getPaginatedOptions($offset, $this->getPerPage(), $search);

        return [
            'options' => $this->transformOptionsForJs($result['options']),
            'hasMore' => $result['hasMore'],
        ];
    }

    public function getExtraAlpineAttributes(): array
    {
        $attributes = parent::getExtraAlpineAttributes();

        if ($this->hasOptionsWithPagination()) {
            $key = $this->getKey();
            $perPage = $this->getPerPage();
            $src = FilamentAsset::getAlpineComponentSrc('infinite-select', 'mrpunyapal/filament-infinite-select');

            $attributes['x-load'] = '';
            $attributes['x-load-src'] = $src;
            $attributes['x-data'] = "infiniteScrollSelect({
                getPaginatedOptionsUsing: async (offset, search) => {
                    return await \$wire.callSchemaComponentMethod('{$key}', 'getPaginatedOptionsForJs', { offset, search })
                },
                perPage: {$perPage},
            })";
        }

        return $attributes;
    }
}
