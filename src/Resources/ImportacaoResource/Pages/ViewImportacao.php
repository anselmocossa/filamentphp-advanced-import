<?php

namespace Filament\AdvancedImport\Resources\ImportacaoResource\Pages;

use Filament\AdvancedImport\Resources\ImportacaoResource\ImportacaoResource;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewImportacao extends ViewRecord
{
    protected static string $resource = ImportacaoResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('advanced-import::messages.resource.details'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('codigo')
                                    ->label(__('advanced-import::messages.resource.codigo'))
                                    ->copyable(),

                                TextEntry::make('categoria')
                                    ->label(__('advanced-import::messages.resource.categoria'))
                                    ->badge(),

                                TextEntry::make('user.name')
                                    ->label(__('advanced-import::messages.resource.user')),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total')
                                    ->label(__('advanced-import::messages.resource.total'))
                                    ->numeric(),

                                TextEntry::make('sucesso')
                                    ->label(__('advanced-import::messages.resource.sucesso'))
                                    ->numeric()
                                    ->color('success'),

                                TextEntry::make('falha')
                                    ->label(__('advanced-import::messages.resource.falha'))
                                    ->numeric()
                                    ->color('danger'),

                                TextEntry::make('success_rate')
                                    ->label(__('advanced-import::messages.resource.success_rate'))
                                    ->formatStateUsing(fn ($record) => number_format($record->success_rate, 1).'%'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('advanced-import::messages.resource.created_at'))
                                    ->dateTime('d/m/Y H:i:s'),

                                TextEntry::make('updated_at')
                                    ->label(__('advanced-import::messages.resource.updated_at'))
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
