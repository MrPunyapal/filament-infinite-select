# Installation

Install the package with Composer:

```bash
composer require mrpunyapal/filament-infinite-select
```

## Publish the JavaScript asset

The Alpine component is registered as a Filament asset and must be copied into your `public/` directory:

```bash
php artisan filament:assets
```

If you skip this step, the select renders but never loads options. The browser requests `/js/mrpunyapal/filament-infinite-select/components/infinite-select.js` and receives a 404.

## Keep assets up to date

Filament assets are static copies. After every `composer update` that changes installed packages, run the command again. To automate it, add it to your project's `post-autoload-dump` script:

```json
"post-autoload-dump": [
    "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
    "@php artisan package:discover --ansi",
    "@php artisan filament:assets"
]
```

Merge this with any entries you already have in that section.
