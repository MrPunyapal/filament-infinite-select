# Pagination types

The closure receives `$offset`, `$limit`, and `$page` (computed for you), so you can use whichever query style fits your data source.

## Offset with skip and take

The simplest approach and the right default for database tables with a stable sort order:

```php
use MrPunyapal\FilamentInfiniteSelect\InfiniteSelect;

InfiniteSelect::make('user_id')
    ->getPaginatedOptionsUsing(function (int $offset, int $limit, ?string $search) {
        $query = User::query()
            ->orderBy('name')
            ->orderBy('id');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return [
            'options' => $query->skip($offset)->take($limit)->pluck('name', 'id')->all(),
            'hasMore' => ($offset + $limit) < User::count(),
        ];
    });
```

Two rules keep offset paging correct at scale: always add a unique tiebreaker column (`orderBy('id')`) next to the sortable column, and index both columns together.

## Laravel paginate with the injected page number

The closure receives `$page` as a 1-based page number computed from the offset. That means you can hand it straight to `paginate()`:

```php
InfiniteSelect::make('user_id')
    ->getPaginatedOptionsUsing(function (int $limit, ?string $search, int $page) {
        $query = Product::query()->orderBy('title');

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'options' => $paginator->pluck('title', 'id')->all(),
            'hasMore' => $paginator->hasMorePages(),
        ];
    });
```

Note that you only need to inject the parameters your closure actually declares.

## Cursor pagination

Cursor pagination avoids scanning skipped rows on very large tables. Because cursors are tied to the previous page, rebuild the starting cursor from the offset first:

```php
use Illuminate\Pagination\Cursor;

InfiniteSelect::make('user_id')
    ->getPaginatedOptionsUsing(function (int $offset, int $limit, ?string $search) {
        $query = User::query()
            ->orderBy('name')
            ->orderBy('id');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $cursor = null;

        if ($offset > 0) {
            $last = (clone $query)->skip($offset - 1)->first();

            if (! $last) {
                return ['options' => [], 'hasMore' => false];
            }

            $cursor = new Cursor(['name' => $last->name, 'id' => $last->id]);
        }

        $rows = (clone $query)->cursorPaginate(perPage: $limit, cursor: $cursor);

        return [
            'options' => $rows->pluck('name', 'id')->all(),
            'hasMore' => $rows->hasMorePages(),
        ];
    });
```

The Cursor keys must match the order columns exactly, including the unique tiebreaker.

## Fetching one extra row instead of counting

For any style, detecting the last page with a single extra row is cheaper than a separate count query:

```php
$rows = $query->skip($offset)->take($limit + 1)->get();

return [
    'options' => $rows->take($limit)->pluck('name', 'id')->all(),
    'hasMore' => $rows->count() > $limit,
];
```

## Which one should I use?

| Situation | Style |
|---|---|
| Database table up to tens of thousands of rows | Offset with skip and take |
| You already have a paginated repository or API returning pages | Laravel paginate with `$page` |
| Very large tables where OFFSET scans get slow | Cursor pagination |
| External API that only supports page numbers | paginate style |
| External API that returns a next-page token | Store the token from each response inside your closure scope or cache, keyed by search term, and request it when `$offset` matches the expected position |

## Remote APIs

The closure can call anything that returns an array of value-label pairs:

```php
InfiniteSelect::make('city_id')
    ->getPaginatedOptionsUsing(function (int $offset, int $limit, ?string $search) {
        $response = Http::get('https://api.example.com/cities', [
            'q' => $search,
            'offset' => $offset,
            'limit' => $limit,
        ])->json();

        return [
            'options' => collect($response['data'])
                ->mapWithKeys(fn (array $city) => [$city['id'] => $city['name']])
                ->all(),
            'hasMore' => $response['meta']['total'] > ($offset + $limit),
        ];
    });
```
