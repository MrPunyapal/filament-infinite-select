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
