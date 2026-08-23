# Configuration

Every option accepts a static value or a closure evaluated at render time.

## perPage

Number of options fetched per page. Default: `15`.

```php
InfiniteSelect::make('user_id')->perPage(25);
```

## preloadFirstPage

When enabled, the first page is rendered server side during the initial request instead of being fetched through a Livewire request when the dropdown opens. Default: `false`.

```php
InfiniteSelect::make('user_id')->preloadFirstPage();
```

Accepts a boolean or a condition:

```php
->preloadFirstPage(fn () => $this->record?->exists)
```

## searchDebounce

Delay in milliseconds between the last keystroke and the search query. Each keystroke resets the timer. Default: `300`.

```php
InfiniteSelect::make('user_id')->searchDebounce(500);
```

## scrollThreshold

Distance in pixels from the bottom of the dropdown at which the next page is requested. Lower values load later, higher values load earlier. Default: `50`.

```php
InfiniteSelect::make('user_id')->scrollThreshold(100);
```

## All options together

```php
InfiniteSelect::make('user_id')
    ->perPage(25)
    ->searchDebounce(500)
    ->scrollThreshold(100)
    ->preloadFirstPage()
    ->getOptionsWithPaginationUsing(function (int $offset, int $limit, ?string $search) {
        // ...
    });
```
