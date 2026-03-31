<?php

namespace Filament\AdvancedImport\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeImportProcessorCommand extends Command
{
    protected $signature = 'import:processor
                            {name : The processor name (e.g., Clientes)}
                            {--category= : The import category}
                            {--model= : The model to import into}
                            {--force : Overwrite existing files}';

    protected $description = 'Create a new import processor';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = $this->argument('name');
        $category = $this->option('category') ?? Str::snake(Str::plural($name));
        $model = $this->option('model') ?? Str::singular($name);

        $processorName = 'Import'.Str::studly(Str::plural($name)).'Processor';
        $path = app_path("Processors/{$processorName}.php");

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->error("Processor already exists: {$path}");
            $this->info('Use --force to overwrite');

            return self::FAILURE;
        }

        // Get stub content
        $stub = $this->getStub();

        // Replace placeholders
        $content = str_replace(
            [
                '{{processorClass}}',
                '{{modelClass}}',
                '{{category}}',
            ],
            [
                $processorName,
                $model,
                $category,
            ],
            $stub
        );

        // Create directory if needed
        $this->files->ensureDirectoryExists(dirname($path));

        // Write file
        $this->files->put($path, $content);

        $this->info("Import processor created: {$path}");
        $this->newLine();
        $this->line('Next steps:');
        $this->line('1. Implement the processItem() method with your import logic');
        $this->line('2. Add the category to config/advanced-import.php:');
        $this->line("   '{$category}' => '".Str::title(str_replace('_', ' ', $category))."',");

        return self::SUCCESS;
    }

    protected function getStub(): string
    {
        $stubPath = __DIR__.'/../../stubs/import-processor.stub';

        if (file_exists($stubPath)) {
            return file_get_contents($stubPath);
        }

        return $this->getDefaultStub();
    }

    protected function getDefaultStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Processors;

use App\Models\{{modelClass}};
use Filament\AdvancedImport\Contracts\ImportProcessor;
use Filament\AdvancedImport\Traits\ProcessesImport;

class {{processorClass}} implements ImportProcessor
{
    use ProcessesImport;

    public function process(array $dados, array $context = []): array
    {
        return $this->processImportBatch(
            dados: $dados,
            categoria: '{{category}}',
            userId: auth()->id()
        );
    }

    protected function processItem(array $item): array
    {
        // Implement your import logic here
        // Example:
        // $record = {{modelClass}}::updateOrCreate(
        //     ['unique_field' => $item['unique_field']],
        //     [
        //         'field1' => $item['field1'],
        //         'field2' => $item['field2'],
        //     ]
        // );

        // return [
        //     'id' => $record->id,
        //     'estado' => 'sucesso',
        // ];

        throw new \Exception('processItem() not implemented');
    }
}
STUB;
    }
}
