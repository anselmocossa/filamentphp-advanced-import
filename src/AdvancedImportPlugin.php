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
        $resources = [];

        if ($this->withNavigation && ImportConfig::shouldRegisterNavigation()) {
            $resources[] = ImportacaoResource::class;
        } elseif (! $this->withNavigation) {
            $resources[] = ImportacaoResource::class;
        } else {
            // Register as a page without navigation — noop since Resource always adds nav
            // Better to not register at all if nav is disabled
        }

        if ($resources) {
            $panel->resources($resources);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
