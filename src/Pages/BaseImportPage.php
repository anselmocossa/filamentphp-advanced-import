<?php

namespace Filament\AdvancedImport\Pages;

use Filament\AdvancedImport\Concerns\HasExcelParsing;
use Filament\AdvancedImport\Concerns\HasImportActions;
use Filament\AdvancedImport\Concerns\HasImportNotifications;
use Filament\AdvancedImport\Concerns\HasImportTable;
use Filament\AdvancedImport\Contracts\ImportProcessor;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

abstract class BaseImportPage extends Page implements HasTable
{
    use HasExcelParsing;
    use HasImportActions;
    use HasImportNotifications;
    use HasImportTable;
    use InteractsWithTable;

    /**
     * Preview data from uploaded file.
     */
    public array $preview = [];

    /**
     * Results after processing.
     */
    public array $resultado = [];

    /**
     * Whether to show results or preview.
     */
    public bool $mostrarResultado = false;

    /**
     * Get the view for the page.
     */
    protected static string $view = 'advanced-import::filament.pages.import-page';

    /**
     * Get the import processor.
     * This must be implemented by child classes.
     */
    abstract protected function getImportProcessor(): ImportProcessor;

    /**
     * Parse a row from the Excel file.
     * This must be implemented by child classes.
     *
     * @param  array  $row  Raw row data from Excel
     * @return array  Parsed row data
     */
    abstract protected function parseRow(array $row): array;

    /**
     * Get table columns for preview/results display.
     * This must be implemented by child classes.
     *
     * @return array<\Filament\Tables\Columns\Column>
     */
    abstract protected function getTableColumns(): array;

    /**
     * Get the page title.
     */
    public function getTitle(): string
    {
        return static::$title ?? __('advanced-import::messages.page.title');
    }

    /**
     * Mount the page.
     */
    public function mount(): void
    {
        $this->preview = [];
        $this->resultado = [];
        $this->mostrarResultado = false;
    }

    /**
     * Get the upload directory for this import.
     * Override to customize per-resource.
     */
    protected function getUploadDirectory(): string
    {
        $resourceName = class_basename(static::$resource);
        $resourceName = str_replace('Resource', '', $resourceName);
        $resourceName = strtolower($resourceName);

        return 'imports/'.$resourceName;
    }
}
