# Filament Advanced Import

[![Latest Version on Packagist](https://img.shields.io/packagist/v/filamentphp/advanced-import.svg?style=flat-square)](https://packagist.org/packages/filamentphp/advanced-import)
[![Total Downloads](https://img.shields.io/packagist/dt/filamentphp/advanced-import.svg?style=flat-square)](https://packagist.org/packages/filamentphp/advanced-import)
[![License](https://img.shields.io/packagist/l/filamentphp/advanced-import.svg?style=flat-square)](LICENSE.md)

Advanced Excel import functionality for Filament 4.x resources with preview, batch processing, and audit trail.

## Features

- **Excel Import with Preview** - Load and preview Excel data before processing
- **Batch Processing** - Process imports in transactions with individual error tracking
- **Audit Trail** - Full import history with detailed logs for each record
- **Customizable Processing** - Define your own parsing and processing logic
- **Multi-language Support** - English and Portuguese translations included
- **Artisan Commands** - Quickly scaffold import pages and processors
- **Filament 4.x & 5.x Ready** - Built for Filament v4 and v5

## Requirements

- PHP 8.2+
- Laravel 11+, 12+ or 13+
- Filament 4.0+ or 5.0+
- Maatwebsite Excel 3.1+ or 4.0+

## Installation

Install the package via Composer:

```bash
composer require filamentphp/advanced-import
```

Run the install command:

```bash
php artisan import:install
```

This will:
1. Publish the configuration file
2. Publish and run the migrations
3. Create the `importacoes` and `importacao_detalhes` tables

### Register the Plugin

Add the plugin to your Filament panel provider:

```php
use Filament\AdvancedImport\AdvancedImportPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            AdvancedImportPlugin::make(),
        ]);
}
```

## Quick Start

### 1. Create an Import Page

Use the Artisan command to generate an import page:

```bash
php artisan import:page ClienteResource --model=Cliente
```

This creates `app/Filament/Resources/Clientes/Pages/ImportClientes.php`.

### 2. Create an Import Processor

Generate a processor for your import logic:

```bash
php artisan import:processor Clientes --category=clientes
```

This creates `app/Processors/ImportClientesProcessor.php`.

### 3. Customize the Import Page

Edit the generated page to define your columns and parsing:

```php
<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Models\Cliente;
use App\Processors\ImportClientesProcessor;
use Filament\AdvancedImport\Contracts\ImportProcessor;
use Filament\AdvancedImport\Pages\BaseImportPage;
use Filament\Tables\Columns\TextColumn;

class ImportClientes extends BaseImportPage
{
    protected static string $resource = ClienteResource::class;
    protected static ?string $title = 'Importar Clientes';

    protected function getImportProcessor(): ImportProcessor
    {
        return new ImportClientesProcessor();
    }

    protected function parseRow(array $row): array
    {
        return [
            'nome' => $row['nome'] ?? $row['name'] ?? '',
            'email' => $row['email'] ?? '',
            'documento' => $row['numero_documento'] ?? $row['document'] ?? '',
            'telefone' => $row['telefone'] ?? $row['phone'] ?? '',
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('nome')
                ->label('Nome')
                ->searchable(),
            TextColumn::make('email')
                ->label('Email'),
            TextColumn::make('documento')
                ->label('Documento'),
            TextColumn::make('telefone')
                ->label('Telefone'),
        ];
    }
}
```

### 4. Customize the Processor

Edit the processor to handle your business logic:

```php
<?php

namespace App\Processors;

use App\Models\Cliente;
use Filament\AdvancedImport\Contracts\ImportProcessor;
use Filament\AdvancedImport\Traits\ProcessesImport;

class ImportClientesProcessor implements ImportProcessor
{
    use ProcessesImport;

    public function process(array $dados, array $context = []): array
    {
        return $this->processImportBatch(
            dados: $dados,
            categoria: 'clientes',
            userId: auth()->id()
        );
    }

    protected function processItem(array $item): array
    {
        // Validate required fields
        if (empty($item['email'])) {
            throw new \Exception('Email é obrigatório');
        }

        // Create or update the record
        $cliente = Cliente::updateOrCreate(
            ['email' => $item['email']],
            [
                'nome' => $item['nome'],
                'numero_documento' => $item['documento'],
                'telefone' => $item['telefone'],
            ]
        );

        return [
            'id' => $cliente->id,
            'nome' => $cliente->nome,
            'email' => $cliente->email,
            'estado' => 'sucesso',
        ];
    }
}
```

### 5. Register the Page in Your Resource

Add the import page to your resource's pages:

```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListClientes::route('/'),
        'create' => Pages\CreateCliente::route('/create'),
        'edit' => Pages\EditCliente::route('/{record}/edit'),
        'import' => Pages\ImportClientes::route('/import'),
    ];
}
```

### 6. Add Navigation Link (Optional)

Add a link to the import page from your list page:

```php
protected function getHeaderActions(): array
{
    return [
        Actions\CreateAction::make(),
        Actions\Action::make('import')
            ->label('Importar Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->url(fn () => static::getResource()::getUrl('import')),
    ];
}
```

## Configuration

Publish the configuration file:

```bash
php artisan import:publish --config
```

### Configuration Options

```php
// config/advanced-import.php

return [
    // File upload settings
    'file' => [
        'max_size' => 10240,  // KB (10MB)
        'disk' => 'public',
        'directory' => 'imports',
        'accepted_types' => [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
    ],

    // Available categories for imports
    'categories' => [
        'clientes' => 'Clientes',
        'contadores' => 'Contadores',
        'contratos' => 'Contratos',
        // Add your custom categories
    ],

    // Database table names
    'tables' => [
        'importacoes' => 'importacoes',
        'importacao_detalhes' => 'importacao_detalhes',
    ],

    // Logging configuration
    'logging' => [
        'channel' => null,  // null = default Laravel channel
        'log_success' => true,
        'log_errors' => true,
    ],

    // Notification settings
    'notifications' => [
        'show_success' => true,
        'show_errors' => true,
        'persistent' => true,
    ],
];
```

## Advanced Usage

### Custom Upload Form

Override the upload form schema in your import page:

```php
protected function getUploadFormSchema(): array
{
    return [
        FileUpload::make('file')
            ->label(__('advanced-import::messages.form.file'))
            ->acceptedFileTypes($this->getAcceptedFileTypes())
            ->maxSize($this->getMaxFileSize())
            ->required(),

        Select::make('month')
            ->label('Mês de Referência')
            ->options([
                1 => 'Janeiro', 2 => 'Fevereiro', /* ... */
            ])
            ->required(),

        TextInput::make('year')
            ->label('Ano')
            ->numeric()
            ->default(now()->year)
            ->required(),
    ];
}
```

### Context Data

Pass additional context to your processor:

```php
protected function getProcessContext(): array
{
    return [
        'month' => $this->data['month'] ?? null,
        'year' => $this->data['year'] ?? null,
        'imported_by' => auth()->user()->name,
    ];
}
```

### Custom Number Parsing

Use the included `NumberParser` for handling different number formats:

```php
use Filament\AdvancedImport\Support\NumberParser;

protected function parseRow(array $row): array
{
    return [
        'valor' => NumberParser::parse($row['valor']),  // Handles "1.234,56" → 1234.56
        'quantidade' => NumberParser::parseInt($row['qtd']),
    ];
}
```

### Pre-Processing Validation

Add validation before processing starts:

```php
protected function validateBeforeProcessing(): void
{
    if (count($this->preview) > 1000) {
        throw new \Exception('Máximo de 1000 registos por importação');
    }

    // Check for required columns
    $firstRow = $this->preview[0] ?? [];
    if (!isset($firstRow['email'])) {
        throw new \Exception('Coluna "email" não encontrada');
    }
}
```

### Custom Result Columns

Show additional fields in the results table:

```php
protected function getResultTableColumns(): array
{
    return [
        TextColumn::make('id')
            ->label('ID'),
        TextColumn::make('nome')
            ->label('Nome'),
        BadgeColumn::make('estado')
            ->colors([
                'success' => 'sucesso',
                'danger' => 'falha',
            ]),
        TextColumn::make('erro')
            ->label('Erro')
            ->wrap()
            ->visible(fn ($record) => $record['estado'] === 'falha'),
    ];
}
```

## Import Audit

The package includes a pre-built `ImportacaoResource` for viewing import history.

### Features

- List all imports with filtering by category, date, and status
- View detailed import information
- See individual record results via RelationManager
- Read-only (no create/edit/delete operations)

### Access

Navigate to **System > Import History** in your Filament panel.

### Customizing the Audit Resource

If you need to customize the audit resource, publish and extend it:

```bash
php artisan import:publish --resources
```

## Artisan Commands

### `import:install`

Install the package (publish config, migrations, run migrations):

```bash
php artisan import:install
```

### `import:page`

Create a new import page:

```bash
php artisan import:page ResourceName --model=ModelName
```

Options:
- `--model` - The Eloquent model class name
- `--force` - Overwrite existing file

### `import:processor`

Create a new import processor:

```bash
php artisan import:processor ProcessorName --category=category_name
```

Options:
- `--category` - The import category for audit tracking
- `--force` - Overwrite existing file

### `import:publish`

Publish package assets:

```bash
php artisan import:publish --config      # Publish configuration
php artisan import:publish --migrations  # Publish migrations
php artisan import:publish --views       # Publish views
php artisan import:publish --lang        # Publish translations
php artisan import:publish --stubs       # Publish stubs
php artisan import:publish --all         # Publish everything
```

## Translations

The package includes English and Portuguese translations. To customize:

```bash
php artisan import:publish --lang
```

This publishes to `resources/lang/vendor/advanced-import/`.

### Adding New Languages

Create a new directory in `resources/lang/vendor/advanced-import/` with your locale code and copy the `messages.php` file from an existing language.

## Upgrading

### From 1.x to 2.x

Run the upgrade migration:

```bash
php artisan migrate
```

Update your import pages to use the new `ImportProcessor` interface.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Anselmo Kossa](https://github.com/anselmocossa)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
