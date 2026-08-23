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
