<?php

namespace Filament\AdvancedImport\Resources\ImportacaoResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DetalhesRelationManager extends RelationManager
{
    protected static string $relationship = 'detalhesRegistros';

    protected static ?string $title = 'Detalhes da Importação';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('advanced-import::messages.resource.status'))
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => in_array($state, ['sucesso', 'success', 'lida', 'criado', 'atualizado']),
                        'danger' => fn ($state) => in_array($state, ['rejeitado', 'failed', 'erro', 'falha']),
                        'warning' => fn ($state) => in_array($state, ['pendente', 'pending']),
                    ]),

                TextColumn::make('payload')
                    ->label(__('advanced-import::messages.resource.payload'))
                    ->limit(50)
                    ->tooltip(fn ($record) => json_encode($record->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                    ->toggleable(),

                TextColumn::make('erro')
                    ->label(__('advanced-import::messages.resource.erro'))
                    ->wrap()
                    ->limit(100)
                    ->color('danger')
                    ->toggleable(),

                TextColumn::make('tempo_ms')
                    ->label(__('advanced-import::messages.resource.tempo'))
                    ->numeric(2)
                    ->suffix(' ms')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('advanced-import::messages.resource.created_at'))
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('advanced-import::messages.resource.status'))
                    ->options([
                        'sucesso' => 'Sucesso',
                        'rejeitado' => 'Rejeitado',
                        'pendente' => 'Pendente',
                    ]),
            ])
            ->defaultSort('id', 'asc')
            ->paginated([10, 25, 50, 100]);
    }
}
