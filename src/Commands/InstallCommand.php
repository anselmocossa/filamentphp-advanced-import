<?php

namespace Filament\AdvancedImport\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'import:install
                            {--no-migrations : Skip running migrations}';

    protected $description = 'Install the Advanced Import package';

    public function handle(): int
    {
        $this->info('Installing Filament Advanced Import...');

        // Publish config
        $this->callSilently('vendor:publish', [
            '--tag' => 'advanced-import-config',
        ]);
        $this->info('✓ Configuration file published');

        // Publish translations
        $this->callSilently('vendor:publish', [
            '--tag' => 'advanced-import-translations',
        ]);
        $this->info('✓ Translations published');

        // Publish migrations
        $this->callSilently('vendor:publish', [
            '--tag' => 'advanced-import-migrations',
        ]);
        $this->info('✓ Migrations published');

        // Run migrations
        if (! $this->option('no-migrations')) {
            if ($this->confirm('Do you want to run the migrations now?', true)) {
                $this->call('migrate');
                $this->info('✓ Migrations executed');
            }
        }

        $this->newLine();
        $this->info('Installation complete!');
        $this->newLine();

        $this->line('Next steps:');
        $this->line('1. Add the plugin to your panel provider:');
        $this->newLine();
        $this->line('   ->plugins([');
        $this->line('       \Filament\AdvancedImport\AdvancedImportPlugin::make(),');
        $this->line('   ])');
        $this->newLine();
        $this->line('2. Create an import page:');
        $this->line('   php artisan import:page YourResource --model=YourModel');
        $this->newLine();
        $this->line('3. Create an import processor:');
        $this->line('   php artisan import:processor YourProcessor --category=your_category');

        return self::SUCCESS;
    }
}
