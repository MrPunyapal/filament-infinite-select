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
