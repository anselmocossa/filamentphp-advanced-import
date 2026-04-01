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
        // Build dynamic columns from first record's payload
        $payloadColumns = $this->getPayloadColumns();

        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Row')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('advanced-import::messages.resource.status'))
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => in_array($state, ['sucesso', 'success', 'lida', 'criado', 'atualizado']),
                        'danger' => fn ($state) => in_array($state, ['rejeitado', 'failed', 'erro', 'falha']),
                        'warning' => fn ($state) => in_array($state, ['pendente', 'pending']),
                    ]),

                ...$payloadColumns,

                TextColumn::make('erro')
                    ->label(__('advanced-import::messages.resource.erro'))
                    ->wrap()
                    ->limit(80)
                    ->tooltip(fn ($record) => $record->erro)
                    ->color('danger')
                    ->placeholder('-'),

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

    /**
     * Generate individual columns for each key in the payload JSON.
     */
    protected function getPayloadColumns(): array
    {
        $columns = [];

        try {
            $firstDetail = $this->getOwnerRecord()
                ->detalhesRegistros()
                ->whereNotNull('payload')
                ->first();

            if ($firstDetail && is_array($firstDetail->payload)) {
                foreach ($firstDetail->payload as $key => $value) {
                    // Skip internal keys
                    if (in_array($key, ['estado', 'id', 'error'])) {
                        continue;
                    }

                    $label = str_replace('_', ' ', ucfirst($key));

                    $columns[] = TextColumn::make("payload.{$key}")
                        ->label($label)
                        ->getStateUsing(fn ($record) => $record->payload[$key] ?? '-')
                        ->limit(40)
                        ->toggleable();
                }
            }
        } catch (\Throwable) {
            // Fallback: show raw payload
            $columns[] = TextColumn::make('payload')
                ->label(__('advanced-import::messages.resource.payload'))
                ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
                ->limit(80)
                ->wrap()
                ->toggleable();
        }

        return $columns;
    }
}
