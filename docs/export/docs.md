# Configuration

# Configuration

All options accept a static value or a closure evaluated at render time.

## `perPage(int | Closure $perPage)`

Number of options fetched per page. Default: `15`.

```php
InfiniteSelect::make('user_id')->perPage(25);
```

## `preloadFirstPage(bool | Closure $condition = true)`

Render the first page server-side during the initial request instead of fetching it via a Livewire round-trip when the dropdown opens. Useful when you want results visible instantly. Default: `false`.

```php
InfiniteSelect::make('user_id')->preloadFirstPage();
```

## `searchDebounce(int | Closure $milliseconds)`

Debounce delay for the search input, in milliseconds. Each keystroke resets the timer; only after this delay is a new page requested from the server. Default: `300`.

```php
InfiniteSelect::make('user_id')->searchDebounce(500);
```

## `scrollThreshold(int | Closure $pixels)`

Distance in pixels from the bottom of the dropdown that triggers loading the next page. Lower values load later; higher values prefetch earlier. Default: `50`.

```php
InfiniteSelect::make('user_id')->scrollThreshold(100);
```

## Combining everything

```php
InfiniteSelect::make('user_id')
    ->perPage(25)
    ->searchDebounce(500)
    ->scrollThreshold(100)
    ->preloadFirstPage(fn () => ! app()->isProduction())
    ->getOptionsWithPaginationUsing(function (int $offset, int $limit, ?string $search) {
        // ...
    });
```


---

# Filament Infinite Select

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


---

# Installation

# Installation

Install the package via Composer:

```bash
composer require mrpunyapal/filament-infinite-select
```

## Publish the JavaScript asset

The Alpine component is registered as a Filament asset. Publish it to your `public/` directory:

```bash
php artisan filament:assets
```

> **Important:** without this step the select will render but never load options — the browser gets a 404 for `infinite-select.js`.

### Keep assets up to date automatically

Add the command to your project's `post-autoload-dump` script so assets refresh on every `composer update`:

```json
"post-autoload-dump": [
    "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
    "@php artisan package:discover --ansi",
    "@php artisan filament:assets"
]
```

(Merge with any existing entries you already have.)

## Requirements

- PHP 8.2+
- Filament 4.x or 5.x
- Livewire (already required by Filament)


---

# Multiple Selection

# Multiple Selection

Enable multi-select with the standard Filament `multiple()` method. The dropdown stays open between picks, and selected options appear as removable badges.

```php
use MrPunyapal\FilamentInfiniteSelect\InfiniteSelect;

InfiniteSelect::make('user_ids')
    ->multiple()
    ->getOptionsWithPaginationUsing(function (int $offset, int $limit, ?string $search) {
        // ...
    })
    ->getOptionLabelsUsing(
        fn (array $values) => User::whereIn('id', $values)->pluck('name', 'id')->all(),
    );
```

## Label resolution for saved values

With lazy loading, saved options may not exist on page one. `getOptionLabelsUsing()` resolves their labels server-side so badges render correctly after a reload:

| Method | Purpose |
|--------|---------|
| `getOptionLabelUsing()` | Single select: label for one saved value |
| `getOptionLabelsUsing()` | Multi select: labels for an array of saved values |

## Persisting arrays

Store selections as JSON when they don't map to a pivot table:

```php
// Migration
$table->json('user_ids')->nullable();

// Model
protected function casts(): array
{
    return [
        'user_ids' => 'array',
    ];
}
```

For relationships, use a standard `BelongsToMany` — the field state is a plain array of values, so it works with `saveRelationships()` out of the box.


---

# Troubleshooting

# Troubleshooting

## Select renders but options never load

The most common cause: the JavaScript asset was never published. The browser requests `/js/mrpunyapal/filament-infinite-select/components/infinite-select.js` and receives a 404, so the infinite scroll component cannot initialize.

```bash
php artisan filament:assets
```

Then hard-refresh the page. To never think about it again, add the command to your `post-autoload-dump` scripts — see [Installation](/installation.md).

## Options load once, then stop

Check that your closure's `hasMore` flag is computed correctly:

- Fetch `$limit + 1` rows and set `hasMore = count > $limit`, or
- Compare against a total: `($offset + $limit) < $total`

If `hasMore` is always `false`, scrolling will not trigger further loads.

## Search returns stale results

Your closure must apply the `$search` parameter to every query — including offset pages. A common mistake is filtering only the first page.

## Saved value shows as raw ID after reload

Provide a label resolver so the component can render saved selections whose options are not in the first loaded page:

```php
->getOptionLabelUsing(fn ($value) => User::find($value)?->name);
```

For multi-select use `getOptionLabelsUsing()` instead — see [Multiple Selection](/multiple-selection.md).

## Values are silently dropped on save

This is standard Laravel mass-assignment protection, not the component. Make sure the attribute is fillable on your model (or that your relationship is configured for Filament's `saveRelationships()`).

## Testing locally with a path repository

When developing against a local clone of this package via a Composer `path` repository, remember the published copy under your app's `public/js/...` is a static snapshot — re-run `php artisan filament:assets` after rebuilding the package's JavaScript.


---

# Usage

# Usage

Use `InfiniteSelect` anywhere you would use Filament's `Select`. The only required method is `getOptionsWithPaginationUsing()`:

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

The component is non-native and searchable by default — no extra configuration needed.

## Closure parameters

The `getOptionsWithPaginationUsing()` closure receives these injected parameters:

| Parameter | Type | Description |
|-----------|------|-------------|
| `$offset` | `int` | Current offset for pagination |
| `$limit` | `int` | Number of items to fetch (from `perPage()`, default 15) |
| `$search` | `?string` | Current search query, if any |
| `$query`  | `?string` | Alias of `$search` |

Plus all standard Filament injection parameters (`$component`, `$get`, `$livewire`, `$record`, ...).

## Return value

Return an array with an `options` key/value map and a `hasMore` flag:

```php
[
    'options' => ['value1' => 'Label 1', 'value2' => 'Label 2'],
    'hasMore' => true, // whether more pages exist after this one
]
```

If the closure returns a plain array without keys, it is treated as the options map with `hasMore: false`. Collections and other `Arrayable` values are converted automatically — returning `->pluck('name', 'id')` directly works fine.

## The `hasMore` flag

The most efficient pattern is fetching **one extra row** to detect the next page instead of running a separate `count()` query:

```php
$rows = $query->skip($offset)->take($limit + 1)->get();
$hasMore = $rows->count() > $limit;

return [
    'options' => ($hasMore ? $rows->pop() : $rows)->pluck('name', 'id')->all(),
    'hasMore' => $hasMore,
];
```

## Displaying saved values

For fields backed by a relationship or column, provide label resolvers so saved selections render correctly even when their options are not on page one:

```php
InfiniteSelect::make('user_id')
    ->getOptionLabelUsing(fn ($value) => User::find($value)?->name);
```

## Headless / standalone usage

The component works outside panels too. In any Livewire component using Filament forms, call the same methods on your schema — assets are resolved automatically via `x-load`, so nothing needs publishing in your app beyond `php artisan filament:assets`.

