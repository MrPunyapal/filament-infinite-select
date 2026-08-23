# Installation

Install the package via Composer:

```bash
composer require mrpunyapal/filament-infinite-select
```

## Publish the JavaScript asset

The Alpine component is registered as a Filament asset. Publish it to your `public/` directory:

```bash
php artisan filament:assets
```

> **Important:** without this step the select will render but never load options — the browser gets a 404 for `infinite-select.js`.

### Keep assets up to date automatically

Add the command to your project's `post-autoload-dump` script so assets refresh on every `composer update`:

```json
"post-autoload-dump": [
    "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
    "@php artisan package:discover --ansi",
    "@php artisan filament:assets"
]
```

(Merge with any existing entries you already have.)

## Requirements

- PHP 8.2+
- Filament 4.x or 5.x
- Livewire (already required by Filament)
