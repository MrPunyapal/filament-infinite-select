# Changelog

All notable changes to `filament-select-with-lazy-loading` will be documented in this file.

## 0.1.0 - 2026-08-24

Initial release.

- `InfiniteSelect` form component extending Filament's `Select` with offset-based lazy loading
- Configurable pagination: `perPage()`, `preloadFirstPage()`, `searchDebounce()`, `scrollThreshold()`
- Options provided via `getOptionsWithPaginationUsing()` closure receiving `$offset`, `$limit`, `$search` / `$query` (plus standard Filament injection)
- Alpine component ships as a registered Filament asset (`x-load`), works in panels and headless Livewire forms
- Supports single and multiple selection, server-side search with debouncing
- Scroll position restore, error/loading states, observer cleanup on teardown
- Nested `Arrayable` results converted automatically
- Requires PHP 8.2+, Filament v4 or v5

**Note:** after installing or updating, run `php artisan filament:assets` to publish the JavaScript asset.
