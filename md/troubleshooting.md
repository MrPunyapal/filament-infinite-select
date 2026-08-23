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
