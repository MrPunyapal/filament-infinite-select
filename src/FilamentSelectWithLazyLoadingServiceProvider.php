<?php

namespace MrPunyapal\FilamentSelectWithLazyLoading;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentSelectWithLazyLoadingServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-select-with-lazy-loading';

    public static string $viewNamespace = 'filament-select-with-lazy-loading';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name);

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageBooted(): void
    {
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );
    }

    protected function getAssetPackageName(): string
    {
        return 'mrpunyapal/filament-select-with-lazy-loading';
    }

    /**
     * @return array<\Filament\Support\Assets\Asset>
     */
    protected function getAssets(): array
    {
        return [
            AlpineComponent::make('infinite-select', __DIR__.'/../resources/js/index.js'),
        ];
    }
}
