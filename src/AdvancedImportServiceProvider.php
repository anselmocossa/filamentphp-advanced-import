<?php

namespace Filament\AdvancedImport;

use Filament\AdvancedImport\Commands\InstallCommand;
use Filament\AdvancedImport\Commands\MakeImportPageCommand;
use Filament\AdvancedImport\Commands\MakeImportProcessorCommand;
use Filament\AdvancedImport\Commands\PublishCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AdvancedImportServiceProvider extends PackageServiceProvider
{
    public static string $name = 'advanced-import';

    public static string $viewNamespace = 'advanced-import';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews(static::$viewNamespace)
            ->hasTranslations()
            ->hasMigrations([
                'create_importacoes_table',
                'create_importacao_detalhes_table',
            ])
            ->hasCommands([
                InstallCommand::class,
                MakeImportPageCommand::class,
                MakeImportProcessorCommand::class,
                PublishCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        parent::packageRegistered();
    }

    public function packageBooted(): void
    {
        parent::packageBooted();

        // Publish stubs
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../stubs' => base_path('stubs/advanced-import'),
            ], 'advanced-import-stubs');
        }
    }
}
