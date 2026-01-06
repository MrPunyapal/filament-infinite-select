<?php

namespace MrPunyapal\FilamentSelectWithLazyLoading;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use MrPunyapal\FilamentSelectWithLazyLoading\Commands\FilamentSelectWithLazyLoadingCommand;
use MrPunyapal\FilamentSelectWithLazyLoading\Testing\TestsFilamentSelectWithLazyLoading;

class FilamentSelectWithLazyLoadingServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-select-with-lazy-loading';

    public static string $viewNamespace = 'filament-select-with-lazy-loading';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('mrpunyapal/filament-select-with-lazy-loading');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-select-with-lazy-loading/{$file->getFilename()}"),
                ], 'filament-select-with-lazy-loading-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentSelectWithLazyLoading);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'mrpunyapal/filament-select-with-lazy-loading';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-select-with-lazy-loading', __DIR__ . '/../resources/dist/components/filament-select-with-lazy-loading.js'),
            Css::make('filament-select-with-lazy-loading-styles', __DIR__ . '/../resources/dist/filament-select-with-lazy-loading.css'),
            Js::make('filament-select-with-lazy-loading-scripts', __DIR__ . '/../resources/dist/filament-select-with-lazy-loading.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentSelectWithLazyLoadingCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filament-select-with-lazy-loading_table',
        ];
    }
}
