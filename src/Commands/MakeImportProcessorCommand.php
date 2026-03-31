<?php

namespace Filament\AdvancedImport\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionMethod;

class MakeImportProcessorCommand extends Command
{
    protected $signature = 'import:processor
                            {name : The processor name (e.g., Products)}
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

        $fields = $this->resolveModelFields($model);
        $content = $this->buildContent($processorName, $model, $category, $fields);

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $content);

        $this->info("Import processor created: {$path}");

        $regularFields = collect($fields)->where('relation', null);
        $relationFields = collect($fields)->whereNotNull('relation');
        $uniqueFields = collect($fields)->where('unique', true);

        if ($regularFields->count() > 0) {
            $this->line('  ✓ '.$regularFields->count().' fields mapped to updateOrCreate()');
        }

        if ($relationFields->count() > 0) {
            $this->line('  ✓ '.$relationFields->count().' relationships with auto-resolve:');

            foreach ($relationFields as $f) {
                $this->line("    - \"{$f['relation']['display']}\" → {$f['relation']['model']}::id");
            }
        }

        if ($uniqueFields->count() > 0) {
            $this->line('  ✓ Unique key: '.implode(', ', $uniqueFields->pluck('name')->toArray()));
        }

        $this->newLine();
        $this->line('Next steps:');
        $this->line("1. Add the category to config/advanced-import.php:");
        $this->line("   '{$category}' => '".Str::title(str_replace('_', ' ', $category))."',");

        return self::SUCCESS;
    }

    /**
     * Resolve model fields with types, relationships, and unique keys.
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

        $relations = $this->detectBelongsToRelations($instance);
        $columnTypes = $this->getColumnTypes($table);
        $uniqueColumns = $this->getUniqueColumns($table);

        $fieldNames = ! empty($fillable) ? $fillable : array_keys($columnTypes);
        $skip = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'email_verified_at', 'password'];
        $fieldNames = array_diff($fieldNames, $skip);

        $fields = [];

        foreach ($fieldNames as $fieldName) {
            $type = $columnTypes[$fieldName] ?? 'string';

            $fields[] = [
                'name' => $fieldName,
                'type' => $type,
                'relation' => $relations[$fieldName] ?? null,
                'unique' => in_array($fieldName, $uniqueColumns),
            ];
        }

        return $fields;
    }

    /**
     * Detect unique columns from database indexes.
     */
    protected function getUniqueColumns(string $table): array
    {
        $unique = [];

        try {
            if (Schema::hasTable($table)) {
                $indexes = Schema::getIndexes($table);

                foreach ($indexes as $index) {
                    if (($index['unique'] ?? false) && count($index['columns'] ?? []) === 1) {
                        $col = $index['columns'][0];

                        if ($col !== 'id') {
                            $unique[] = $col;
                        }
                    }
                }
            }
        } catch (\Throwable) {
            // Ignore
        }

        return $unique;
    }

    /**
     * Detect belongsTo relationships via reflection + _id fallback.
     */
    protected function detectBelongsToRelations(object $instance): array
    {
        $relations = [];
        $class = get_class($instance);
        $reflection = new \ReflectionClass($class);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== $class || $method->getNumberOfParameters() > 0) {
                continue;
            }

            $methodName = $method->getName();

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
                continue;
            }
        }

        // Fallback: _id columns without explicit relationships
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

                        $relations[$name] = [
                            'method' => Str::camel(Str::replaceLast('_id', '', $name)),
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
     * Build the processor PHP file content.
     */
    protected function buildContent(string $processorName, string $model, string $category, array $fields): string
    {
        $useStatements = $this->generateUseStatements($model, $fields);
        $resolveLines = $this->generateRelationResolvers($fields);
        $validationLines = $this->generateValidation($fields);
        $uniqueKeyLines = $this->generateUniqueKeys($fields);
        $updateFields = $this->generateUpdateFields($fields);
        $returnFields = $this->generateReturnFields($fields);

        return <<<PHP
<?php

namespace App\\Processors;

{$useStatements}
use Filament\\AdvancedImport\\Contracts\\ImportProcessor;
use Filament\\AdvancedImport\\Traits\\ProcessesImport;

class {$processorName} implements ImportProcessor
{
    use ProcessesImport;

    public function process(array \$dados, array \$context = []): array
    {
        return \$this->processImportBatch(
            dados: \$dados,
            categoria: '{$category}',
            userId: auth()->id()
        );
    }

    protected function processItem(array \$item): array
    {
{$validationLines}
{$resolveLines}
        \$record = {$model}::updateOrCreate(
{$uniqueKeyLines}
{$updateFields}
        );

        return [
            'id' => \$record->id,
{$returnFields}
            'estado' => 'sucesso',
        ];
    }
}
PHP;
    }

    protected function generateUseStatements(string $model, array $fields): string
    {
        $models = ["App\\Models\\{$model}"];

        foreach ($fields as $field) {
            if ($field['relation'] ?? null) {
                $fqn = $field['relation']['model_fqn'];

                if (! in_array($fqn, $models)) {
                    $models[] = $fqn;
                }
            }
        }

        sort($models);

        return implode("\n", array_map(fn ($m) => "use {$m};", $models));
    }

    protected function generateValidation(array $fields): string
    {
        // Require non-nullable string/text fields
        $required = [];

        foreach ($fields as $field) {
            if ($field['relation'] ?? null) {
                $readableKey = Str::replaceLast('_id', '', $field['name']);
                $required[] = $readableKey;
            } elseif ($field['unique'] ?? false) {
                $required[] = $field['name'];
            }
        }

        if (empty($required)) {
            // Use first string field as required
            foreach ($fields as $field) {
                if (($field['type'] ?? '') === 'string' && ! ($field['relation'] ?? null)) {
                    $required[] = $field['name'];
                    break;
                }
            }
        }

        if (empty($required)) {
            return '';
        }

        $checks = implode(' || ', array_map(fn ($f) => "empty(\$item['{$f}'])", $required));
        $names = implode(', ', $required);

        return "        if ({$checks}) {\n".
               "            throw new \\Exception('{$names} required');\n".
               "        }\n";
    }

    protected function generateRelationResolvers(array $fields): string
    {
        $lines = [];

        foreach ($fields as $field) {
            $relation = $field['relation'] ?? null;

            if (! $relation) {
                continue;
            }

            $fk = $field['name'];
            $readableKey = Str::replaceLast('_id', '', $fk);
            $relModel = $relation['model'];
            $display = $relation['display'];
            $var = Str::camel($readableKey);

            $lines[] = "        \${$var} = {$relModel}::where('{$display}', \$item['{$readableKey}'] ?? '')->first();";
            $lines[] = '';
            $lines[] = "        if (! \${$var} && ! empty(\$item['{$readableKey}'])) {";
            $lines[] = "            throw new \\Exception(\"{$relModel} not found: {\$item['{$readableKey}']}\");";
            $lines[] = '        }';
            $lines[] = '';
        }

        return empty($lines) ? '' : implode("\n", $lines)."\n";
    }

    protected function generateUniqueKeys(array $fields): string
    {
        $unique = [];

        foreach ($fields as $field) {
            if ($field['unique'] ?? false) {
                $name = $field['name'];
                $unique[] = "                '{$name}' => \$item['{$name}'],";
            }
        }

        if (empty($unique)) {
            // Fallback: use first string field
            foreach ($fields as $field) {
                if (($field['type'] ?? '') === 'string' && ! ($field['relation'] ?? null)) {
                    $name = $field['name'];
                    $unique[] = "                '{$name}' => \$item['{$name}'],";
                    break;
                }
            }
        }

        if (empty($unique)) {
            $unique[] = "                'id' => \$item['id'] ?? null,";
        }

        return '            ['."\n".implode("\n", $unique)."\n".'            ],';
    }

    protected function generateUpdateFields(array $fields): string
    {
        $lines = [];

        foreach ($fields as $field) {
            $name = $field['name'];
            $relation = $field['relation'] ?? null;

            // Skip unique keys (they go in the first array)
            if ($field['unique'] ?? false) {
                continue;
            }

            if ($relation) {
                $readableKey = Str::replaceLast('_id', '', $name);
                $var = Str::camel($readableKey);
                $lines[] = "                '{$name}' => \${$var}?->id,";
            } else {
                $lines[] = "                '{$name}' => \$item['{$name}'] ?? null,";
            }
        }

        if (empty($lines)) {
            return '            []';
        }

        return '            ['."\n".implode("\n", $lines)."\n".'            ]';
    }

    protected function generateReturnFields(array $fields): string
    {
        $lines = [];
        $count = 0;

        foreach ($fields as $field) {
            if ($count >= 3) {
                break;
            }

            $name = $field['name'];

            if ($field['relation'] ?? null) {
                continue;
            }

            if ($field['type'] === 'string') {
                $lines[] = "            '{$name}' => \$record->{$name},";
                $count++;
            }
        }

        return implode("\n", $lines);
    }
}
