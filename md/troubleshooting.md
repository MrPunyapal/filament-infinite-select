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
