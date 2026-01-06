# This is my package filament-select-with-lazy-loading

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrpunyapal/filament-select-with-lazy-loading.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/filament-select-with-lazy-loading)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mrpunyapal/filament-select-with-lazy-loading/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mrpunyapal/filament-select-with-lazy-loading/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mrpunyapal/filament-select-with-lazy-loading/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/mrpunyapal/filament-select-with-lazy-loading/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mrpunyapal/filament-select-with-lazy-loading.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/filament-select-with-lazy-loading)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require mrpunyapal/filament-select-with-lazy-loading
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-select-with-lazy-loading-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-select-with-lazy-loading-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-select-with-lazy-loading-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filamentSelectWithLazyLoading = new MrPunyapal\FilamentSelectWithLazyLoading();
echo $filamentSelectWithLazyLoading->echoPhrase('Hello, MrPunyapal!');
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
