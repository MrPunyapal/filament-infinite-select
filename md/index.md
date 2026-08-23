# Filament Infinite Select

A Filament Select component with infinite scroll lazy loading for options. Perfect for handling large datasets without loading all options at once.

Instead of hydrating thousands of options into the dropdown, **InfiniteSelect** loads one page at a time as the user scrolls — with debounced server-side search, preloading support, and full compatibility with Filament's native select behavior.

## Features

- Infinite scroll pagination over any dataset (Eloquent queries, APIs, arrays)
- Configurable page size, scroll threshold, and search debounce
- Optional server-side preload of the first page
- Single and multiple selection
- Works in panels and headless Livewire forms — ships as a registered Filament asset, no view publishing required
- Scroll position restore, loading and error states out of the box
- Supports Filament v4 and v5

## Requirements

| Package | Version |
|---------|---------|
| PHP | 8.2+ |
| Filament Forms | ^4.0 \| ^5.0 |

## Quick example

```php
use MrPunyapal\FilamentInfiniteSelect\InfiniteSelect;

InfiniteSelect::make('user_id')
    ->getOptionsWithPaginationUsing(function (int $offset, int $limit, ?string $search) {
        $query = User::query()->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $total = (clone $query)->count();

        return [
            'options' => $query->skip($offset)->take($limit)->pluck('name', 'id')->all(),
            'hasMore' => ($offset + $limit) < $total,
        ];
    })
    ->getOptionLabelUsing(fn ($value) => User::find($value)?->name);
```

## Credits

- [Punyapal Shah](https://github.com/mrpunyapal)
- [All contributors](https://github.com/mrpunyapal/filament-infinite-select/graphs/contributors)

## License

The MIT License (MIT). See [License](https://github.com/mrpunyapal/filament-infinite-select/blob/main/LICENSE.md) for details.
