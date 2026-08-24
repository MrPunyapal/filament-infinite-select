<?php

declare(strict_types=1);

use Filament\Forms\Components\Select;
use Illuminate\Contracts\Support\Arrayable;
use MrPunyapal\FilamentInfiniteSelect\InfiniteSelect;

test('infinite select component can be instantiated', function () {
    $select = InfiniteSelect::make('test');

    expect($select)->toBeInstanceOf(InfiniteSelect::class);
    expect($select)->toBeInstanceOf(Select::class);
});

test('infinite select is non-native by default', function () {
    $select = InfiniteSelect::make('test');

    expect($select->isNative())->toBeFalse();
});

test('infinite select is searchable by default', function () {
    $select = InfiniteSelect::make('test');

    expect($select->isSearchable())->toBeTrue();
});

test('per page can be configured', function () {
    $select = InfiniteSelect::make('test')
        ->perPage(25);

    expect($select->getPerPage())->toBe(25);
});

test('per page defaults to 15', function () {
    $select = InfiniteSelect::make('test');

    expect($select->getPerPage())->toBe(15);
});

test('per page accepts closure', function () {
    $select = InfiniteSelect::make('test')
        ->perPage(fn () => 50);

    expect($select->getPerPage())->toBe(50);
});

test('preload first page is disabled by default', function () {
    $select = InfiniteSelect::make('test');

    expect($select->shouldPreloadFirstPage())->toBeFalse();
});

test('preload first page can be enabled', function () {
    $select = InfiniteSelect::make('test')
        ->preloadFirstPage();

    expect($select->shouldPreloadFirstPage())->toBeTrue();
});

test('preload first page can be conditionally enabled', function () {
    $condition = true;
    $select = InfiniteSelect::make('test')
        ->preloadFirstPage(fn () => $condition);

    expect($select->shouldPreloadFirstPage())->toBeTrue();

    $condition = false;
    $select2 = InfiniteSelect::make('test2')
        ->preloadFirstPage(fn () => $condition);

    expect($select2->shouldPreloadFirstPage())->toBeFalse();
});

test('has options with pagination returns false when no callback set', function () {
    $select = InfiniteSelect::make('test');

    expect($select->hasPaginatedOptions())->toBeFalse();
});

test('has options with pagination returns true when callback is set', function () {
    $select = InfiniteSelect::make('test')
        ->getPaginatedOptionsUsing(fn () => []);

    expect($select->hasPaginatedOptions())->toBeTrue();
});

test('get paginated options returns empty when no callback set', function () {
    $select = InfiniteSelect::make('test');

    $result = $select->getPaginatedOptions(0, 15);

    expect($result)->toBe([
        'options' => [],
        'hasMore' => false,
    ]);
});

test('get paginated options returns data from callback', function () {
    $select = InfiniteSelect::make('test')
        ->getPaginatedOptionsUsing(function (int $offset, int $limit) {
            return [
                'options' => ['1' => 'Option 1', '2' => 'Option 2'],
                'hasMore' => true,
            ];
        });

    $result = $select->getPaginatedOptions(0, 15);

    expect($result)->toBe([
        'options' => ['1' => 'Option 1', '2' => 'Option 2'],
        'hasMore' => true,
    ]);
});

test('get paginated options passes offset and limit to callback', function () {
    $receivedOffset = null;
    $receivedLimit = null;

    $select = InfiniteSelect::make('test')
        ->getPaginatedOptionsUsing(function (int $offset, int $limit) use (&$receivedOffset, &$receivedLimit) {
            $receivedOffset = $offset;
            $receivedLimit = $limit;

            return ['options' => [], 'hasMore' => false];
        });

    $select->getPaginatedOptions(50, 25);

    expect($receivedOffset)->toBe(50);
    expect($receivedLimit)->toBe(25);
});

test('get paginated options passes search query to callback', function () {
    $receivedSearch = null;

    $select = InfiniteSelect::make('test')
        ->getPaginatedOptionsUsing(function (int $offset, int $limit, ?string $search) use (&$receivedSearch) {
            $receivedSearch = $search;

            return ['options' => [], 'hasMore' => false];
        });

    $select->getPaginatedOptions(0, 15, 'test search');

    expect($receivedSearch)->toBe('test search');
});

test('get paginated options handles callback returning just array', function () {
    $select = InfiniteSelect::make('test')
        ->getPaginatedOptionsUsing(function () {
            return ['1' => 'Option 1', '2' => 'Option 2'];
        });

    $result = $select->getPaginatedOptions(0, 15);

    expect($result)->toBe([
        'options' => ['1' => 'Option 1', '2' => 'Option 2'],
        'hasMore' => false,
    ]);
});

test('get paginated options handles arrayable results', function () {
    $pages = collect(['1' => 'Option 1', '2' => 'Option 2']);

    expect($pages)->toBeInstanceOf(Arrayable::class);

    $select = InfiniteSelect::make('test')
        ->getPaginatedOptionsUsing(function () use ($pages) {
            return [
                'options' => $pages,
                'hasMore' => false,
            ];
        });

    $result = $select->getPaginatedOptions(0, 15);

    expect($result['options'])->toBeArray();
    expect(array_values($result['options']))->toBe(['Option 1', 'Option 2']);
    expect($result['hasMore'])->toBeFalse();
});

test('get paginated options for js transforms options correctly', function () {
    $select = InfiniteSelect::make('test')
        ->perPage(10)
        ->getPaginatedOptionsUsing(function () {
            return [
                'options' => ['1' => 'Option 1', '2' => 'Option 2'],
                'hasMore' => true,
            ];
        });

    $result = $select->getPaginatedOptionsForJs(0, null);

    expect($result)->toHaveKey('options');
    expect($result)->toHaveKey('hasMore');
    expect($result['hasMore'])->toBeTrue();
    expect($result['options'])->toBeArray()->toHaveCount(2);
    expect($result['options'][0])->toHaveKeys(['value', 'label']);
    // Values are stringified for JS consumption
    expect($result['options'][0]['value'])->toBe('1');
    expect($result['options'][1]['value'])->toBe('2');
    expect($result['options'][0]['label'])->toBe('Option 1');
});

test('get paginated options for js uses configured per page', function () {
    $receivedLimit = null;

    $select = InfiniteSelect::make('test')
        ->perPage(25)
        ->getPaginatedOptionsUsing(function (int $offset, int $limit) use (&$receivedLimit) {
            $receivedLimit = $limit;

            return ['options' => [], 'hasMore' => false];
        });

    $select->getPaginatedOptionsForJs(0, null);

    expect($receivedLimit)->toBe(25);
});

test('pagination works with simulated dataset', function () {
    // Simulate a dataset of 100 items
    $dataset = collect(range(1, 100))
        ->mapWithKeys(fn (int $id) => [$id => "Option {$id}"])
        ->all();

    $select = InfiniteSelect::make('test')
        ->perPage(10)
        ->getPaginatedOptionsUsing(function (int $offset, int $limit) use ($dataset) {
            $slice = array_slice($dataset, $offset, $limit + 1, preserve_keys: true);
            $hasMore = count($slice) > $limit;

            if ($hasMore) {
                array_pop($slice);
            }

            return [
                'options' => $slice,
                'hasMore' => $hasMore,
            ];
        });

    // First page
    $result1 = $select->getPaginatedOptions(0, 10);
    expect($result1['options'])->toHaveCount(10);
    expect($result1['hasMore'])->toBeTrue();

    // Second page
    $result2 = $select->getPaginatedOptions(10, 10);
    expect($result2['options'])->toHaveCount(10);
    expect($result2['hasMore'])->toBeTrue();

    // Last page
    $result3 = $select->getPaginatedOptions(90, 10);
    expect($result3['options'])->toHaveCount(10);
    expect($result3['hasMore'])->toBeFalse();
});

test('search filters results correctly', function () {
    $dataset = collect([
        1 => 'Unique Test Page Title',
        2 => 'Another Page',
        3 => 'Third Page',
    ]);

    $select = InfiniteSelect::make('test')
        ->perPage(50)
        ->getPaginatedOptionsUsing(function (int $offset, int $limit, ?string $search) use ($dataset) {
            $filtered = $dataset->filter(
                fn (string $title) => $search === null || str_contains(strtolower($title), strtolower($search)),
            );

            $slice = array_slice($filtered->all(), $offset, $limit + 1, preserve_keys: true);
            $hasMore = count($slice) > $limit;

            if ($hasMore) {
                array_pop($slice);
            }

            return [
                'options' => $slice,
                'hasMore' => $hasMore,
            ];
        });

    $result = $select->getPaginatedOptions(0, 50, 'Unique Test Page');

    expect($result['options'])->toHaveCount(1);
    $firstOptionLabel = array_values($result['options'])[0];
    expect($firstOptionLabel)->toContain('Unique Test Page Title');
});

test('has more flag is false when results are less than limit', function () {
    $dataset = [
        1 => 'Option 1',
        2 => 'Option 2',
        3 => 'Option 3',
        4 => 'Option 4',
        5 => 'Option 5',
    ];

    $select = InfiniteSelect::make('test')
        ->perPage(10)
        ->getPaginatedOptionsUsing(function (int $offset, int $limit) use ($dataset) {
            $slice = array_slice($dataset, $offset, $limit + 1, preserve_keys: true);
            $hasMore = count($slice) > $limit;

            if ($hasMore) {
                array_pop($slice);
            }

            return [
                'options' => $slice,
                'hasMore' => $hasMore,
            ];
        });

    $result = $select->getPaginatedOptions(0, 10);

    expect($result['options'])->toHaveCount(5);
    expect($result['hasMore'])->toBeFalse();
});

test('component renders with correct view', function () {
    $select = InfiniteSelect::make('test')
        ->getPaginatedOptionsUsing(fn () => ['options' => [], 'hasMore' => false]);

    expect($select->getView())->toBe('filament-infinite-select::infinite-select');
});

test('pagination handles empty search results', function () {
    $select = InfiniteSelect::make('test')
        ->perPage(10)
        ->getPaginatedOptionsUsing(function (int $offset, int $limit, ?string $search) {
            if ($search === 'NonExistentSearchTerm12345') {
                return ['options' => [], 'hasMore' => false];
            }

            return ['options' => [1 => 'Option 1'], 'hasMore' => false];
        });

    $result = $select->getPaginatedOptions(0, 10, 'NonExistentSearchTerm12345');

    expect($result['options'])->toBeEmpty();
    expect($result['hasMore'])->toBeFalse();
});

test('pagination callback receives query parameter alias', function () {
    $receivedQuery = null;

    $select = InfiniteSelect::make('test')
        ->getPaginatedOptionsUsing(function (int $offset, int $limit, ?string $search, ?string $query) use (&$receivedQuery) {
            $receivedQuery = $query;

            return ['options' => [], 'hasMore' => false];
        });

    $select->getPaginatedOptions(0, 15, 'test search');

    expect($receivedQuery)->toBe('test search');
});

test('search debounce can be configured', function () {
    $select = InfiniteSelect::make('test')
        ->searchDebounce(500);

    expect($select->getSearchDebounce())->toBe(500);
});

test('search debounce defaults to 300', function () {
    $select = InfiniteSelect::make('test');

    expect($select->getSearchDebounce())->toBe(300);
});

test('search debounce accepts closure', function () {
    $select = InfiniteSelect::make('test')
        ->searchDebounce(fn () => 1000);

    expect($select->getSearchDebounce())->toBe(1000);
});

test('scroll threshold can be configured', function () {
    $select = InfiniteSelect::make('test')
        ->scrollThreshold(100);

    expect($select->getScrollThreshold())->toBe(100);
});

test('scroll threshold defaults to 50', function () {
    $select = InfiniteSelect::make('test');

    expect($select->getScrollThreshold())->toBe(50);
});

test('scroll threshold accepts closure', function () {
    $select = InfiniteSelect::make('test')
        ->scrollThreshold(fn () => 75);

    expect($select->getScrollThreshold())->toBe(75);
});

test('deprecated getOptionsWithPaginationUsing delegates to getPaginatedOptionsUsing', function () {
    $select = InfiniteSelect::make('test')
        ->getOptionsWithPaginationUsing(fn () => ['options' => ['1' => 'Option 1'], 'hasMore' => false]);

    expect($select->hasPaginatedOptions())->toBeTrue();
    expect($select->getPaginatedOptions(0, 15))->toBe([
        'options' => ['1' => 'Option 1'],
        'hasMore' => false,
    ]);
});

test('deprecated hasOptionsWithPagination delegates to hasPaginatedOptions', function () {
    $select = InfiniteSelect::make('test')
        ->getPaginatedOptionsUsing(fn () => []);

    expect($select->hasOptionsWithPagination())->toBeTrue();
});

test('pagination callback receives computed page number', function () {
    $receivedPage = null;

    $select = InfiniteSelect::make('test')
        ->perPage(15)
        ->getPaginatedOptionsUsing(function (int $offset, int $limit, ?string $search, int $page) use (&$receivedPage) {
            $receivedPage = $page;

            return ['options' => [], 'hasMore' => false];
        });

    $select->getPaginatedOptions(30, 15);

    expect($receivedPage)->toBe(3);
});

test('page number resets correctly for first page and handles zero limit safely', function () {
    $pages = [];

    $select = InfiniteSelect::make('test')
        ->getPaginatedOptionsUsing(function (int $offset, int $limit) use (&$pages) {
            $pages[] = (intdiv($offset, max($limit, 1)) + 1);

            return ['options' => [], 'hasMore' => false];
        });

    $select->getPaginatedOptions(0, 15);
    $select->getPaginatedOptions(15, 15);
    $select->getPaginatedOptions(45, 15);

    expect($pages)->toBe([1, 2, 4]);
});
