<?php

namespace Filament\AdvancedImport\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeImportPageCommand extends Command
{
    protected $signature = 'import:page
                            {resource : The resource name (e.g., ClienteResource)}
                            {--model= : The model name (e.g., Cliente)}
                            {--force : Overwrite existing files}';

    protected $description = 'Create a new import page for a Filament resource';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $resource = $this->argument('resource');
        $model = $this->option('model') ?? Str::replaceLast('Resource', '', class_basename($resource));

        // Determine paths
        $resourcePath = $this->getResourcePath($resource);
        $pagePath = $resourcePath.'/Pages/Import'.Str::plural($model).'.php';

        if ($this->files->exists($pagePath) && ! $this->option('force')) {
            $this->error("Import page already exists: {$pagePath}");
            $this->info('Use --force to overwrite');

            return self::FAILURE;
        }

        // Get stub content
        $stub = $this->getStub();

        // Replace placeholders
        $content = str_replace(
            [
                '{{namespace}}',
                '{{resourceClass}}',
                '{{modelClass}}',
                '{{modelPlural}}',
                '{{modelVariable}}',
                '{{processorClass}}',
            ],
            [
                $this->getNamespace($resource),
                class_basename($resource),
                $model,
                Str::plural($model),
                Str::camel($model),
                'Import'.Str::plural($model).'Processor',
            ],
            $stub
        );

        // Create directory if needed
        $this->files->ensureDirectoryExists(dirname($pagePath));

        // Write file
        $this->files->put($pagePath, $content);

        $this->info("Import page created: {$pagePath}");
        $this->newLine();
        $this->line('Next steps:');
        $this->line('1. Implement the parseRow() method to map Excel columns');
        $this->line('2. Implement the getTableColumns() method for preview table');
        $this->line('3. Create a processor: php artisan import:processor '.Str::plural($model));
        $this->line('4. Register the page in your resource\'s getPages() method');

        return self::SUCCESS;
    }

    protected function getResourcePath(string $resource): string
    {
        // Handle fully qualified class name
        if (Str::contains($resource, '\\')) {
            $parts = explode('\\', $resource);
            $resourceName = array_pop($parts);

            return app_path('Filament/Resources/'.Str::replaceLast('Resource', '', $resourceName));
        }

        return app_path('Filament/Resources/'.Str::replaceLast('Resource', '', $resource));
    }

    protected function getNamespace(string $resource): string
    {
        if (Str::contains($resource, '\\')) {
            $parts = explode('\\', $resource);
            array_pop($parts);

            return implode('\\', $parts).'\\Pages';
        }

        $resourceName = Str::replaceLast('Resource', '', $resource);

        return 'App\\Filament\\Resources\\'.$resourceName.'\\Pages';
    }

    protected function getStub(): string
    {
        $stubPath = __DIR__.'/../../stubs/import-page.stub';

        if (file_exists($stubPath)) {
            return file_get_contents($stubPath);
        }

        return $this->getDefaultStub();
    }

    protected function getDefaultStub(): string
    {
        return <<<'STUB'
<?php

namespace {{namespace}};

use App\Filament\Resources\{{resourceClass}};
use App\Processors\{{processorClass}};
use Filament\AdvancedImport\Contracts\ImportProcessor;
use Filament\AdvancedImport\Pages\BaseImportPage;
use Filament\Tables\Columns\TextColumn;

class Import{{modelPlural}} extends BaseImportPage
{
    protected static string $resource = {{resourceClass}}::class;

    protected static ?string $title = 'Importar {{modelPlural}}';

    protected static ?string $navigationLabel = 'Importar';

    protected function getImportProcessor(): ImportProcessor
    {
        return new {{processorClass}}();
    }

    protected function parseRow(array $row): array
    {
        return [
            // Map your Excel columns here
            // 'field_name' => $row['excel_column'] ?? '',
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            // Add your preview columns here
            // TextColumn::make('field_name')->label('Field Name'),
        ];
    }
}
STUB;
    }
}
