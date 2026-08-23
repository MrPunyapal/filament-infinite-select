# Filament Infinite Select

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrpunyapal/filament-infinite-select.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/filament-infinite-select)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mrpunyapal/filament-infinite-select/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mrpunyapal/filament-infinite-select/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mrpunyapal/filament-infinite-select.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/filament-infinite-select)

A Filament Select component with infinite scroll lazy loading for options. Perfect for handling large datasets without loading all options at once.

## Requirements

- PHP 8.2+
- Filament 4.x / 5.x

Works in any Filament context — panels, standalone Livewire forms (headless), or custom schema components. The Alpine component ships as a registered Filament asset, so no view publishing is required.

## Installation

```bash
composer require mrpunyapal/filament-infinite-select
```

Then publish the JavaScript asset:

```bash
php artisan filament:assets
```

> **Tip:** To keep assets up to date automatically after every `composer update`, add this to your project's `composer.json`:
>
> ```json
> "post-autoload-dump": [
>     "@php artisan filament:assets"
> ]
> ```
>
> (merging with any existing `post-autoload-dump` entries)

## Usage

### Basic Usage

```php
use MrPunyapal\FilamentInfiniteSelect\InfiniteSelect;

InfiniteSelect::make('user_id')
    ->getOptionsWithPaginationUsing(function (int $offset, int $limit, ?string $search) {
        $query = User::query();
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        $total = $query->count();
        $options = $query
            ->orderBy('name')
            ->offset($offset)
            ->limit($limit)
            ->pluck('name', 'id')
            ->all();
        
        return [
            'options' => $options,
            'hasMore' => ($offset + $limit) < $total,
        ];
    })
    ->getOptionLabelUsing(fn ($value) => User::find($value)?->name);
```

### Customizing Per Page

```php
InfiniteSelect::make('user_id')
    ->perPage(25)
    ->getOptionsWithPaginationUsing(function (int $offset, int $limit, ?string $search) {
        // ...
    });
```

### Available Methods

| Method | Default | Description |
|--------|---------|-------------|
| `perPage(int \| Closure)` | `15` | Number of options to fetch per page |
| `preloadFirstPage(bool \| Closure)` | `false` | Load the first page server-side during render, instead of via a Livewire round-trip when the dropdown opens |
| `searchDebounce(int \| Closure)` | `300` | Debounce delay in milliseconds for the search input |
| `scrollThreshold(int \| Closure)` | `50` | Distance in pixels from the bottom of the dropdown that triggers loading the next page |

```php
InfiniteSelect::make('user_id')
    ->perPage(25)
    ->searchDebounce(500)
    ->scrollThreshold(100)
    ->preloadFirstPage()
    ->getOptionsWithPaginationUsing(function (int $offset, int $limit, ?string $search) {
        // ...
    });
```

### With Multiple Selection

```php
InfiniteSelect::make('user_ids')
    ->multiple()
    ->getOptionsWithPaginationUsing(function (int $offset, int $limit, ?string $search) {
        // ...
    })
    ->getOptionLabelsUsing(fn (array $values) => User::whereIn('id', $values)->pluck('name', 'id')->all());
```

### Closure Parameters

The `getOptionsWithPaginationUsing` closure receives the following parameters:

| Parameter | Type | Description |
|-----------|------|-------------|
| `$offset` | `int` | The current offset for pagination |
| `$limit` | `int` | The number of items to fetch (from `perPage()`) |
| `$search` | `?string` | The current search query, if any |

Plus all standard Filament injection parameters (`$component`, `$get`, `$livewire`, `$record`, etc.)

### Return Value

The closure should return an array with:

```php
[
    'options' => ['value1' => 'Label 1', 'value2' => 'Label 2'],
    'hasMore' => true, // Whether there are more options to load
]
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Punyapal Shah](https://github.com/MrPunyapal)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
