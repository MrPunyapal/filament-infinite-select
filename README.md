# Filament Infinite Select

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrpunyapal/filament-infinite-select.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/filament-infinite-select)
[![Total Downloads](https://img.shields.io/packagist/dt/mrpunyapal/filament-infinite-select.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/filament-infinite-select)
[![Laravel Compatibility](https://badge.laravel.cloud/badge/MrPunyapal/filament-infinite-select)](https://packagist.org/packages/MrPunyapal/filament-infinite-select)
[![Filament Compatibility](https://badge.laravel.cloud/filament-badge/MrPunyapal/filament-infinite-select)](https://packagist.org/packages/MrPunyapal/filament-infinite-select)
[![PHP Compatibility](https://badge.laravel.cloud/php-badge/MrPunyapal/filament-infinite-select)](https://packagist.org/packages/MrPunyapal/filament-infinite-select)
[![Laravel Boost](https://badge.laravel.cloud/boost-badge.svg)](https://github.com/laravel/boost)

A Filament form component that loads select options one page at a time as the user scrolls. Use it when a select can contain hundreds or thousands of options and loading them all at once is too slow.

Options are fetched through a closure you provide. The dropdown appends each page on scroll, search queries your closure server side with debouncing, and saved values resolve their labels through separate closures so they display correctly even when they are not on the first page.

## Requirements

- PHP 8.2 or higher
- Filament 4.x or 5.x
- Laravel 11.28 or higher

## Documentation

The documentation is available at [mrpunyapal.github.io/filament-infinite-select](https://mrpunyapal.github.io/filament-infinite-select).

- [Installation](https://mrpunyapal.github.io/filament-infinite-select/installation)
- [Usage](https://mrpunyapal.github.io/filament-infinite-select/usage)
- [Configuration](https://mrpunyapal.github.io/filament-infinite-select/configuration)
- [Multiple selection](https://mrpunyapal.github.io/filament-infinite-select/multiple-selection)
- [Troubleshooting](https://mrpunyapal.github.io/filament-infinite-select/troubleshooting)

Using AI agents (Claude Code, Cursor, Boost, ...)? The package ships an installable skill:

```bash
npx skills add MrPunyapal/filament-infinite-select/resources/boost/skills
# or, in a Laravel project with Boost:
php artisan boost:install
```

## Quick example

```php
use MrPunyapal\FilamentInfiniteSelect\InfiniteSelect;

InfiniteSelect::make('user_id')
    ->getOptionsWithPaginationUsing(function (int $offset, int $limit, ?string $search) {
        $query = User::query()->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $total = (clone $query)->count();

        return [
            'options' => $query->skip($offset)->take($limit)->pluck('name', 'id')->all(),
            'hasMore' => ($offset + $limit) < $total,
        ];
    })
    ->getOptionLabelUsing(fn ($value) => User::find($value)?->name);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Punyapal Shah](https://github.com/MrPunyapal)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
