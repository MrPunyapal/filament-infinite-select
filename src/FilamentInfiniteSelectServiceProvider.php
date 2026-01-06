<?php

namespace MrPunyapal\FilamentInfiniteSelect;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentInfiniteSelectServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-infinite-select';

    public static string $viewNamespace = 'filament-infinite-select';

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
        return 'mrpunyapal/filament-infinite-select';
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
