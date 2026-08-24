---
name: infinite-select
description: "Use when adding or debugging lazy-loading select fields in Filament forms with MrPunyapal/FilamentInfiniteSelect: pagination closure contract, hasMore detection, label resolvers for saved values, multiple selection storage, and asset publishing."
license: MIT
metadata:
  author: mrpunyapal
---

# InfiniteSelect Usage Guidelines

InfiniteSelect is a Filament form component that loads select options one page at a time as the user scrolls. Use it instead of `Select::make()` when a field can have hundreds or thousands of options.

## Required setup

Every InfiniteSelect needs a pagination closure. It receives `$offset`, `$limit`, and `$search` by name:

```php
use MrPunyapal\FilamentInfiniteSelect\InfiniteSelect;

InfiniteSelect::make('user_id')
    ->getOptionsWithPaginationUsing(function (int $offset, int $limit, ?string $search) {
        $query = User::query()->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return [
            'options' => $query->skip($offset)->take($limit)->pluck('name', 'id')->all(),
            'hasMore' => ($offset + $limit) < User::count(),
        ];
    });
```

Rules for the closure:

- Apply `$search` on every page of results, not only offset 0.
- Return `'options'` as a value-to-label array and `'hasMore'` as a bool.
- Collections are fine; they are converted automatically.
- Prefer fetching `$limit + 1` rows over a separate `count()` query:

```php
$rows = $query->skip($offset)->take($limit + 1)->get();

return [
    'options' => $rows->take($limit)->pluck('name', 'id')->all(),
    'hasMore' => $rows->count() > $limit,
];
```

## Saved values need label resolvers

Loaded pages may not contain previously saved values. Without a resolver, edit forms show raw IDs:

```php
// Single select
->getOptionLabelUsing(fn ($value) => User::find($value)?->name)

// Multiple select
->getOptionLabelsUsing(fn (array $values) => User::whereIn('id', $values)->pluck('name', 'id')->all())
```

## Multiple selection

Add `->multiple()`. State is a plain array. Store it as JSON with an `'array'` cast when there is no pivot table, or use a standard `BelongsToMany`.

## Configuration options

| Method | Default | Controls |
|---|---|---|
| `perPage()` | 15 | Rows per page |
| `preloadFirstPage()` | false | Render page one server side |
| `searchDebounce()` | 300 | Search delay in ms |
| `scrollThreshold()` | 50 | Pixels from bottom that trigger loading |

All accept closures evaluated at render time.

## Asset publishing

The Alpine component is a Filament asset. If the select renders but never loads options, check for a 404 on `/js/mrpunyapal/filament-infinite-select/components/infinite-select.js` and run:

```bash
php artisan filament:assets
```

When working against a local clone through a Composer path repository, re-run this command after rebuilding the package JavaScript.
