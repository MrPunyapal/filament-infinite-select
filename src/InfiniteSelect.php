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
    protected string $view = 'filament-infinite-select::infinite-select';

    protected ?Closure $getOptionsWithPaginationUsing = null;

    protected int | Closure $perPage = 15;

    protected bool | Closure $preloadFirstPage = false;

    protected int | Closure $searchDebounce = 300;

    protected int | Closure $scrollThreshold = 50;

    protected function setUp(): void
    {
        parent::setUp();

        $this->native(false);
        $this->searchable();
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

    public function preloadFirstPage(bool | Closure $condition = true): static
    {
        $this->preloadFirstPage = $condition;

        return $this;
    }

    public function shouldPreloadFirstPage(): bool
    {
        return (bool) $this->evaluate($this->preloadFirstPage);
    }

    public function searchDebounce(int | Closure $milliseconds): static
    {
        $this->searchDebounce = $milliseconds;

        return $this;
    }

    public function getSearchDebounce(): int
    {
        return (int) $this->evaluate($this->searchDebounce);
    }

    public function scrollThreshold(int | Closure $pixels): static
    {
        $this->scrollThreshold = $pixels;

        return $this;
    }

    public function getScrollThreshold(): int
    {
        return (int) $this->evaluate($this->scrollThreshold);
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

        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

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

    public function getInfiniteScrollAlpineSrc(): string
    {
        return FilamentAsset::getAlpineComponentSrc('infinite-select', 'mrpunyapal/filament-infinite-select');
    }
}
