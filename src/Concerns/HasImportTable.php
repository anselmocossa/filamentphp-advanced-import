<?php

namespace Filament\AdvancedImport\Concerns;

use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

trait HasImportTable
{
    /**
     * Configure the table for displaying import data.
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns($this->getAllTableColumns())
            ->records(fn (): array => $this->mostrarResultado ? $this->resultado : $this->preview)
            ->paginated(false)
            ->emptyStateHeading(__('advanced-import::messages.table.empty_heading'))
            ->emptyStateDescription(__('advanced-import::messages.table.empty_description'));
    }

    /**
     * Get all table columns including status and error columns.
     */
    protected function getAllTableColumns(): array
    {
        return array_merge(
            $this->getTableColumns(),
            $this->getStatusColumns()
        );
    }

    /**
     * Get the main table columns.
     * This method must be implemented by the page class.
     *
     * @return array<\Filament\Tables\Columns\Column>
     */
    abstract protected function getTableColumns(): array;

    /**
     * Get the status and error columns.
     */
    protected function getStatusColumns(): array
    {
        return [
            TextColumn::make('estado')
                ->label(__('advanced-import::messages.table.status'))
                ->badge()
                ->colors([
                    'success' => fn ($state) => in_array($state, ['sucesso', 'success', 'lida', 'criado', 'atualizado']),
                    'danger' => fn ($state) => in_array($state, ['rejeitado', 'failed', 'erro', 'falha']),
                    'warning' => fn ($state) => in_array($state, ['pendente', 'pending', 'estimada']),
                    'gray' => fn ($state) => ! in_array($state, ['sucesso', 'success', 'lida', 'criado', 'atualizado', 'rejeitado', 'failed', 'erro', 'falha', 'pendente', 'pending', 'estimada']),
                ])
                ->icons([
                    'heroicon-o-check-circle' => fn ($state) => in_array($state, ['sucesso', 'success', 'lida', 'criado', 'atualizado']),
                    'heroicon-o-x-circle' => fn ($state) => in_array($state, ['rejeitado', 'failed', 'erro', 'falha']),
                    'heroicon-o-exclamation-triangle' => fn ($state) => in_array($state, ['pendente', 'pending', 'estimada']),
                ]),

            TextColumn::make('error')
                ->label(__('advanced-import::messages.table.error'))
                ->wrap()
                ->toggleable(isToggledHiddenByDefault: true)
                ->color('danger'),
        ];
    }

    /**
     * Configure the page content schema.
     */
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make($this->getTableSectionTitle())
                ->description($this->getTableSectionDescription())
                ->schema([
                    EmbeddedTable::make(),
                ])
                ->columns(1),
        ]);
    }

    /**
     * Get the table section title.
     */
    protected function getTableSectionTitle(): string
    {
        if ($this->mostrarResultado) {
            return __('advanced-import::messages.section.results');
        }

        return __('advanced-import::messages.section.preview');
    }

    /**
     * Get the table section description.
     */
    protected function getTableSectionDescription(): ?string
    {
        $count = $this->mostrarResultado ? count($this->resultado) : count($this->preview);

        if ($count === 0) {
            return null;
        }

        return __('advanced-import::messages.section.row_count', ['count' => $count]);
    }
}
