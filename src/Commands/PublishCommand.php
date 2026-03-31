<?php

namespace Filament\AdvancedImport\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'import:publish
                            {--config : Publish configuration file}
                            {--migrations : Publish migration files}
                            {--views : Publish view files}
                            {--lang : Publish translation files}
                            {--stubs : Publish stub files}
                            {--all : Publish all assets}
                            {--force : Overwrite existing files}';

    protected $description = 'Publish Advanced Import package assets';

    public function handle(): int
    {
        $options = [];

        if ($this->option('force')) {
            $options['--force'] = true;
        }

        $published = false;

        if ($this->option('all') || $this->option('config')) {
            $this->call('vendor:publish', array_merge($options, [
                '--tag' => 'advanced-import-config',
            ]));
            $this->info('Configuration file published.');
            $published = true;
        }

        if ($this->option('all') || $this->option('migrations')) {
            $this->call('vendor:publish', array_merge($options, [
                '--tag' => 'advanced-import-migrations',
            ]));
            $this->info('Migration files published.');
            $published = true;
        }

        if ($this->option('all') || $this->option('views')) {
            $this->call('vendor:publish', array_merge($options, [
                '--tag' => 'advanced-import-views',
            ]));
            $this->info('View files published.');
            $published = true;
        }

        if ($this->option('all') || $this->option('lang')) {
            $this->call('vendor:publish', array_merge($options, [
                '--tag' => 'advanced-import-translations',
            ]));
            $this->info('Translation files published.');
            $published = true;
        }

        if ($this->option('all') || $this->option('stubs')) {
            $this->call('vendor:publish', array_merge($options, [
                '--tag' => 'advanced-import-stubs',
            ]));
            $this->info('Stub files published.');
            $published = true;
        }

        if (! $published) {
            $this->warn('No assets published. Use one of the following options:');
            $this->line('  --config      Publish configuration file');
            $this->line('  --migrations  Publish migration files');
            $this->line('  --views       Publish view files');
            $this->line('  --lang        Publish translation files');
            $this->line('  --stubs       Publish stub files');
            $this->line('  --all         Publish all assets');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
