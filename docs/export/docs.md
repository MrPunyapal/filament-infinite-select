# Configuration

# Configuration

Every option accepts a static value or a closure evaluated at render time.

## perPage

Number of options fetched per page. Default: `15`.

```php
InfiniteSelect::make('user_id')->perPage(25);
```

## preloadFirstPage

When enabled, the first page is rendered server side during the initial request instead of being fetched through a Livewire request when the dropdown opens. Default: `false`.

```php
InfiniteSelect::make('user_id')->preloadFirstPage();
```

Accepts a boolean or a condition:

```php
->preloadFirstPage(fn () => $this->record?->exists)
```

## searchDebounce

Delay in milliseconds between the last keystroke and the search query. Each keystroke resets the timer. Default: `300`.

```php
InfiniteSelect::make('user_id')->searchDebounce(500);
```

## scrollThreshold

Distance in pixels from the bottom of the dropdown at which the next page is requested. Lower values load later, higher values load earlier. Default: `50`.

```php
InfiniteSelect::make('user_id')->scrollThreshold(100);
```

## All options together

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


---

# Filament Infinite Select

# Filament Infinite Select

A Filament form component that loads select options one page at a time as the user scrolls. Use it when a select can contain hundreds or thousands of options and loading all of them at once is too slow.

## How it works

You provide a closure that receives an offset, a limit, and the current search term, and returns one page of options. The component handles the rest:

- The first page loads when the dropdown opens (or renders server side with `preloadFirstPage()`)
- Scrolling near the bottom of the list fetches the next page and appends it
- Search input queries your closure with debouncing, resets to page one, and replaces the list
- Saved values resolve their labels through `getOptionLabelUsing()` or `getOptionLabelsUsing()`, so they display even when they are not in the loaded pages
- The scroll position is kept when the dropdown is closed and reopened

## Requirements

| Package | Version |
|---------|---------|
| PHP | 8.2 or higher |
| Laravel | 11.28 or higher |
| Filament Forms | 4.x or 5.x |

The component works in panels and in standalone Livewire applications that use Filament forms.

## Credits

- [Punyapal Shah](https://github.com/mrpunyapal)
- [All contributors](https://github.com/mrpunyapal/filament-infinite-select/graphs/contributors)

## License

The MIT License (MIT). See [License](https://github.com/mrpunyapal/filament-infinite-select/blob/main/LICENSE.md) for details.


---

# Installation

# Installation

Install the package with Composer:

```bash
composer require mrpunyapal/filament-infinite-select
```

## Publish the JavaScript asset

The Alpine component is registered as a Filament asset and must be copied into your `public/` directory:

```bash
php artisan filament:assets
```

If you skip this step, the select renders but never loads options. The browser requests `/js/mrpunyapal/filament-infinite-select/components/infinite-select.js` and receives a 404.

## Keep assets up to date

Filament assets are static copies. After every `composer update` that changes installed packages, run the command again. To automate it, add it to your project's `post-autoload-dump` script:

```json
"post-autoload-dump": [
    "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
    "@php artisan package:discover --ansi",
    "@php artisan filament:assets"
]
```

Merge this with any entries you already have in that section.


---

# Multiple selection

# Multiple selection

Add `multiple()` to allow several picks. The dropdown stays open between picks and each selected option shows as a removable badge.

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

## Label resolution

Saved options may not be in the loaded pages. The `getOptionLabelsUsing()` closure resolves their labels server side so the badges render with names instead of raw IDs after a reload.

| Method | Used for |
|--------|----------|
| `getOptionLabelUsing()` | Single select, one saved value |
| `getOptionLabelsUsing()` | Multi select, an array of saved values |

## Storing the values

The field state is a plain array of values. For pivot tables, a standard `BelongsToMany` relationship works with Filament's `saveRelationships()`. Without a relationship table, store the array as JSON:

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


---

# Troubleshooting

# Troubleshooting

## The select renders but options never load

The JavaScript asset has not been published. Check whether this URL returns a 404 in your browser network tab:

```
/js/mrpunyapal/filament-infinite-select/components/infinite-select.js
```

Run `php artisan filament:assets` and reload the page. See [Installation](/installation.md) for how to automate this.

## Options stop loading after the first page

Check how your closure computes `hasMore`. If it is always `false`, scrolling will not request more pages. Either compare against a total:

```php
'hasMore' => ($offset + $limit) < $total,
```

or fetch one extra row and compare:

```php
$rows = $query->skip($offset)->take($limit + 1)->get();
$hasMore = $rows->count() > $limit;
```

Also make sure `$search` is applied on every page of a search, not only the first.

## A saved value shows as its raw ID after reload

The field cannot resolve labels for options that are not in the loaded pages. Add a label resolver:

```php
// Single select
->getOptionLabelUsing(fn ($value) => User::find($value)?->name)

// Multi select
->getOptionLabelsUsing(fn (array $values) => User::whereIn('id', $values)->pluck('name', 'id')->all())
```

## Field values are dropped when saving

This is Laravel mass assignment protection, not the component. Make sure the attribute is fillable on your model, or use a Filament relationship so `saveRelationships()` handles persistence.

## Local development with a path repository

When you require the package from a local clone through a Composer `path` repository, the published file under your app's `public/js/...` is a copy, not a link. After rebuilding the package JavaScript, run `php artisan filament:assets` again to refresh it.


---

# Usage

# Usage

Use `InfiniteSelect` in place of Filament's `Select`. The only required method is `getOptionsWithPaginationUsing()`:

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

The component sets `native(false)` and `searchable()` for you.

## Closure parameters

`getOptionsWithPaginationUsing()` receives these parameters by name:

| Parameter | Type | Description |
|-----------|------|-------------|
| `$offset` | `int` | Number of rows to skip |
| `$limit` | `int` | Number of rows to return, taken from `perPage()` |
| `$search` | `?string` | Current search term |
| `$query` | `?string` | Alias for `$search` |

Standard Filament injection (`$component`, `$get`, `$livewire`, `$record`, and so on) also works.

## Return value

Return an array with an options map under the `options` key and a boolean under `hasMore`:

```php
[
    'options' => ['value1' => 'Label 1', 'value2' => 'Label 2'],
    'hasMore' => true,
]
```

Three shortcuts are supported:

- Returning a plain array with no `options` key treats it as the options map with `hasMore: false`
- Collections and other `Arrayable` values are converted to arrays, so returning `->pluck('name', 'id')` directly is fine
- The same conversions apply to the `options` value itself when it is an `Arrayable`

## Detecting the next page

Comparing against a total count works but runs a second query. Fetching one extra row avoids that:

```php
$rows = $query->skip($offset)->take($limit + 1)->get();
$hasMore = $rows->count() > $limit;

return [
    'options' => $rows->take($limit)->pluck('name', 'id')->all(),
    'hasMore' => $hasMore,
];
```

## Displaying saved values

Saved selections may not be part of the loaded pages. Give the field a label resolver so they display correctly on edit forms:

```php
// Single select
->getOptionLabelUsing(fn ($value) => User::find($value)?->name);
```

For multiple selection use `getOptionLabelsUsing()`. See [Multiple selection](/multiple-selection.md).

