# Configuration

All options accept a static value or a closure evaluated at render time.

## `perPage(int | Closure $perPage)`

Number of options fetched per page. Default: `15`.

```php
InfiniteSelect::make('user_id')->perPage(25);
```

## `preloadFirstPage(bool | Closure $condition = true)`

Render the first page server-side during the initial request instead of fetching it via a Livewire round-trip when the dropdown opens. Useful when you want results visible instantly. Default: `false`.

```php
InfiniteSelect::make('user_id')->preloadFirstPage();
```

## `searchDebounce(int | Closure $milliseconds)`

Debounce delay for the search input, in milliseconds. Each keystroke resets the timer; only after this delay is a new page requested from the server. Default: `300`.

```php
InfiniteSelect::make('user_id')->searchDebounce(500);
```

## `scrollThreshold(int | Closure $pixels)`

Distance in pixels from the bottom of the dropdown that triggers loading the next page. Lower values load later; higher values prefetch earlier. Default: `50`.

```php
InfiniteSelect::make('user_id')->scrollThreshold(100);
```

## Combining everything

```php
InfiniteSelect::make('user_id')
    ->perPage(25)
    ->searchDebounce(500)
    ->scrollThreshold(100)
    ->preloadFirstPage(fn () => ! app()->isProduction())
    ->getOptionsWithPaginationUsing(function (int $offset, int $limit, ?string $search) {
        // ...
    });
```
