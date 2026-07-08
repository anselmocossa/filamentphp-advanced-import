<?php

namespace Filament\AdvancedImport;

use Filament\AdvancedImport\Resources\ImportacaoResource\ImportacaoResource;
use Filament\AdvancedImport\Support\ImportConfig;
use Filament\Contracts\Plugin;
use Filament\Panel;

class AdvancedImportPlugin implements Plugin
{
    protected bool $withNavigation = true;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'advanced-import';
    }

    public function withoutNavigation(): static
    {
        $this->withNavigation = false;

        return $this;
    }

    public function register(Panel $panel): void
    {
        if ($this->withNavigation && ImportConfig::shouldRegisterNavigation()) {
            $panel->resources([
                ImportacaoResource::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
