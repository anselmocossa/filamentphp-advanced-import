<?php

namespace Filament\AdvancedImport\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionMethod;

class MakeImportPageCommand extends Command
{
    protected $signature = 'import:page
                            {resource : The resource name (e.g., ProductResource)}
                            {--model= : The model name (e.g., Product)}
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

        $fields = $this->resolveModelFields($model);

        $resourcePath = $this->getResourcePath($resource);
        $pagePath = $resourcePath.'/Pages/Import'.Str::plural($model).'.php';

        if ($this->files->exists($pagePath) && ! $this->option('force')) {
            $this->error("Import page already exists: {$pagePath}");
            $this->info('Use --force to overwrite');

            return self::FAILURE;
        }

        $content = $this->buildContent($resource, $model, $fields);

        $this->files->ensureDirectoryExists(dirname($pagePath));
        $this->files->put($pagePath, $content);

        $this->info("Import page created: {$pagePath}");

        $regularFields = collect($fields)->where('relation', null);
        $relationFields = collect($fields)->whereNotNull('relation');

        if ($regularFields->count() > 0) {
            $this->line('  ✓ '.$regularFields->count().' regular fields mapped');
        }

        if ($relationFields->count() > 0) {
            $this->line('  ✓ '.$relationFields->count().' relationships detected:');

            foreach ($relationFields as $f) {
                $this->line("    - {$f['name']} → {$f['relation']['model']}::{$f['relation']['display']}");
            }
        }

        $this->newLine();
        $this->line('Next steps:');
        $this->line('1. Create a processor: php artisan import:processor '.Str::plural($model).' --model='.$model);
        $this->line('2. Register the page in your resource\'s getPages() method');

        return self::SUCCESS;
    }

    /**
     * Resolve all fields from the model, including relationship detection.
     */
    protected function resolveModelFields(string $model): array
    {
        $modelClass = $this->resolveModelClass($model);

        if (! $modelClass) {
            return [];
        }

        $instance = null;
        $fillable = [];
        $table = null;

        try {
            $instance = new $modelClass;
            $fillable = $instance->getFillable();
            $table = $instance->getTable();
        } catch (\Throwable) {
            return [];
        }

        // Detect belongsTo relationships via reflection
        $relations = $this->detectBelongsToRelations($instance);

        // Get column types from schema
        $columnTypes = $this->getColumnTypes($table);

        // Determine which fields to process
        $fieldNames = ! empty($fillable) ? $fillable : array_keys($columnTypes);
        $skip = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'email_verified_at', 'password'];
        $fieldNames = array_diff($fieldNames, $skip);

        $fields = [];

        foreach ($fieldNames as $fieldName) {
            $type = $columnTypes[$fieldName] ?? 'string';

            // Check if this is a foreign key with a detected relationship
            if (isset($relations[$fieldName])) {
                $fields[] = [
                    'name' => $fieldName,
                    'type' => $type,
                    'relation' => $relations[$fieldName],
                ];
            } else {
                $fields[] = [
                    'name' => $fieldName,
                    'type' => $type,
                    'relation' => null,
                ];
            }
        }

        return $fields;
    }

    /**
     * Detect belongsTo relationships by inspecting model methods.
     * Returns a map of foreign_key => ['method' => 'category', 'model' => 'Category', 'display' => 'name']
     */
    protected function detectBelongsToRelations(object $instance): array
    {
        $relations = [];
        $class = get_class($instance);
        $reflection = new \ReflectionClass($class);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Skip non-model methods
            if ($method->class !== $class) {
                continue;
            }

            if ($method->getNumberOfParameters() > 0) {
                continue;
            }

            $methodName = $method->getName();

            // Skip common non-relation methods
            if (in_array($methodName, ['boot', 'booted', 'booting', 'getTable', 'getFillable', 'getCasts', 'toArray'])) {
                continue;
            }

            try {
                $result = $instance->{$methodName}();

                if ($result instanceof BelongsTo) {
                    $relatedModel = get_class($result->getRelated());
                    $foreignKey = $result->getForeignKeyName();
                    $displayColumn = $this->guessDisplayColumn($result->getRelated());

                    $relations[$foreignKey] = [
                        'method' => $methodName,
                        'model' => class_basename($relatedModel),
                        'model_fqn' => $relatedModel,
                        'display' => $displayColumn,
                        'foreign_key' => $foreignKey,
                    ];
                }
            } catch (\Throwable) {
                // Method might throw errors, skip it
            }
        }

        // Fallback: detect _id columns without explicit relationships
        try {
            $table = $instance->getTable();

            if (Schema::hasTable($table)) {
                foreach (Schema::getColumns($table) as $column) {
                    $name = $column['name'];

                    if (! str_ends_with($name, '_id') || isset($relations[$name])) {
                        continue;
                    }

                    $guessedModel = Str::studly(Str::replaceLast('_id', '', $name));
                    $guessedClass = "App\\Models\\{$guessedModel}";

                    if (class_exists($guessedClass)) {
                        $relatedInstance = new $guessedClass;
                        $displayColumn = $this->guessDisplayColumn($relatedInstance);
                        $guessedMethod = Str::camel(Str::replaceLast('_id', '', $name));

                        $relations[$name] = [
                            'method' => $guessedMethod,
                            'model' => $guessedModel,
                            'model_fqn' => $guessedClass,
                            'display' => $displayColumn,
                            'foreign_key' => $name,
                        ];
                    }
                }
            }
        } catch (\Throwable) {
            // Ignore
        }

        return $relations;
    }

    /**
     * Guess the best display column for a related model (name, title, label, etc.)
     */
    protected function guessDisplayColumn(object $model): string
    {
        $candidates = ['name', 'nome', 'title', 'titulo', 'label', 'description', 'descricao', 'email', 'code', 'codigo', 'slug'];

        try {
            $table = $model->getTable();

            if (Schema::hasTable($table)) {
                $columns = collect(Schema::getColumns($table))->pluck('name')->toArray();

                foreach ($candidates as $candidate) {
                    if (in_array($candidate, $columns)) {
                        return $candidate;
                    }
                }

                // Return first string column that isn't id/timestamps
                $skip = ['id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];

                foreach ($columns as $col) {
                    if (! in_array($col, $skip) && ! str_ends_with($col, '_id')) {
                        return $col;
                    }
                }
            }
        } catch (\Throwable) {
            // Ignore
        }

        return 'name';
    }

    protected function getColumnTypes(string $table): array
    {
        $types = [];

        try {
            if (Schema::hasTable($table)) {
                foreach (Schema::getColumns($table) as $column) {
                    $types[$column['name']] = $this->mapColumnType($column['type_name'] ?? 'varchar');
                }
            }
        } catch (\Throwable) {
            // Ignore
        }

        return $types;
    }

    protected function mapColumnType(string $dbType): string
    {
        $dbType = strtolower($dbType);

        return match (true) {
            str_contains($dbType, 'int') => 'integer',
            str_contains($dbType, 'decimal'), str_contains($dbType, 'float'),
            str_contains($dbType, 'double'), str_contains($dbType, 'numeric') => 'float',
            str_contains($dbType, 'bool') => 'boolean',
            str_contains($dbType, 'date'), str_contains($dbType, 'timestamp') => 'date',
            default => 'string',
        };
    }

    protected function resolveModelClass(string $model): ?string
    {
        foreach (["App\\Models\\{$model}", "App\\{$model}"] as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Build the full PHP file content.
     */
    protected function buildContent(string $resource, string $model, array $fields): string
    {
        $namespace = $this->getNamespace($resource);
        $resourceClass = class_basename($resource);
        $modelPlural = Str::plural($model);
        $processorClass = 'Import'.$modelPlural.'Processor';

        $parseRowLines = $this->generateParseRow($fields);
        $tableColumnLines = $this->generateTableColumns($fields);

        return <<<PHP
<?php

namespace {$namespace};

use App\\Filament\\Resources\\{$resourceClass};
use App\\Processors\\{$processorClass};
use Filament\\AdvancedImport\\Contracts\\ImportProcessor;
use Filament\\AdvancedImport\\Pages\\BaseImportPage;
use Filament\\Tables\\Columns\\TextColumn;

class Import{$modelPlural} extends BaseImportPage
{
    protected static string \$resource = {$resourceClass}::class;

    protected static ?string \$title = 'Import {$modelPlural}';

    protected static ?string \$navigationLabel = 'Import';

    protected function getImportProcessor(): ImportProcessor
    {
        return new {$processorClass}();
    }

    protected function parseRow(array \$row): array
    {
        return [
{$parseRowLines}
        ];
    }

    protected function getTableColumns(): array
    {
        return [
{$tableColumnLines}
        ];
    }
}
PHP;
    }

    protected function generateParseRow(array $fields): string
    {
        if (empty($fields)) {
            return "            // No model fields detected — map your Excel columns here:\n".
                   "            // 'field_name' => \$this->getString(\$row, 'excel_column'),";
        }

        $lines = [];

        foreach ($fields as $field) {
            $name = $field['name'];
            $type = $field['type'];
            $relation = $field['relation'] ?? null;

            if ($relation) {
                // For relationships: read the human-readable column from Excel
                $display = $relation['display'];
                $readableKey = Str::replaceLast('_id', '', $name);
                $lines[] = "            '{$readableKey}' => \$this->getString(\$row, '{$readableKey}'),  // → {$relation['model']}::{$display}";
            } else {
                $method = match ($type) {
                    'integer' => "(int) \$this->getFloat(\$row, '{$name}')",
                    'float' => "\$this->getFloat(\$row, '{$name}')",
                    'boolean' => "(bool) \$this->getString(\$row, '{$name}')",
                    'date' => "\$this->getDate(\$row, '{$name}')",
                    default => "\$this->getString(\$row, '{$name}')",
                };

                $lines[] = "            '{$name}' => {$method},";
            }
        }

        return implode("\n", $lines);
    }

    protected function generateTableColumns(array $fields): string
    {
        if (empty($fields)) {
            return "            // No model fields detected — add your columns here:\n".
                   "            // TextColumn::make('field_name')->label('Field Name'),";
        }

        $lines = [];

        foreach ($fields as $field) {
            $name = $field['name'];
            $type = $field['type'];
            $relation = $field['relation'] ?? null;

            if ($relation) {
                // Show the human-readable name, not the ID
                $readableKey = Str::replaceLast('_id', '', $name);
                $label = Str::headline($readableKey);
                $lines[] = "            TextColumn::make('{$readableKey}')->label('{$label}')->badge(),";
            } else {
                $label = Str::headline($name);
                $column = "            TextColumn::make('{$name}')->label('{$label}')";

                $column .= match ($type) {
                    'integer' => '->numeric()',
                    'float' => '->numeric(2)',
                    'boolean' => '->badge()',
                    'date' => "->date('d/m/Y')",
                    default => '',
                };

                if ($type === 'string' && preg_match('/(status|estado|type|tipo|category|categoria)/', $name)) {
                    $column .= '->badge()';
                }

                $lines[] = $column.',';
            }
        }

        return implode("\n", $lines);
    }

    protected function getResourcePath(string $resource): string
    {
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
}
