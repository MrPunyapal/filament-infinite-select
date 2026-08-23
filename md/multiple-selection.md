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
