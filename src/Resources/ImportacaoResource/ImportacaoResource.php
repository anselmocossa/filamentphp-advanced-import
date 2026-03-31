<?php

namespace Filament\AdvancedImport\Resources\ImportacaoResource;

use Filament\AdvancedImport\Models\Importacao;
use Filament\AdvancedImport\Resources\ImportacaoResource\Pages\ListImportacoes;
use Filament\AdvancedImport\Resources\ImportacaoResource\Pages\ViewImportacao;
use Filament\AdvancedImport\Resources\ImportacaoResource\RelationManagers\DetalhesRelationManager;
use Filament\AdvancedImport\Support\ImportConfig;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ImportacaoResource extends Resource
{
    protected static ?string $model = Importacao::class;

    protected static ?string $slug = 'importacoes';

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = 'Importação';

    protected static ?string $pluralModelLabel = 'Importações';

    public static function getNavigationIcon(): string
    {
        return ImportConfig::getNavigationIcon();
    }

    public static function getNavigationGroup(): ?string
    {
        return ImportConfig::getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return ImportConfig::getNavigationSort();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label(__('advanced-import::messages.resource.codigo'))
                    ->searchable()
                    ->copyable()
                    ->limit(20),

                TextColumn::make('categoria')
                    ->label(__('advanced-import::messages.resource.categoria'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => ImportConfig::getCategoryLabel($state))
                    ->searchable(),

                TextColumn::make('total')
                    ->label(__('advanced-import::messages.resource.total'))
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('sucesso')
                    ->label(__('advanced-import::messages.resource.sucesso'))
                    ->numeric()
                    ->alignCenter()
                    ->color('success'),

                TextColumn::make('falha')
                    ->label(__('advanced-import::messages.resource.falha'))
                    ->numeric()
                    ->alignCenter()
                    ->color('danger'),

                TextColumn::make('user.name')
                    ->label(__('advanced-import::messages.resource.user'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('advanced-import::messages.resource.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('categoria')
                    ->label(__('advanced-import::messages.resource.categoria'))
                    ->options(ImportConfig::getCategories()),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                \Filament\Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            DetalhesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListImportacoes::route('/'),
            'view' => ViewImportacao::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
