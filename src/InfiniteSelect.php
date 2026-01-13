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

    public function getInfiniteScrollAlpineSrc(): string
    {
        return FilamentAsset::getAlpineComponentSrc('infinite-select', 'mrpunyapal/filament-infinite-select');
    }
}
