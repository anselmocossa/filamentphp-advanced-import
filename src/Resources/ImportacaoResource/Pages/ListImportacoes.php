<?php

namespace Filament\AdvancedImport\Resources\ImportacaoResource\Pages;

use Filament\AdvancedImport\Resources\ImportacaoResource\ImportacaoResource;
use Filament\Resources\Pages\ListRecords;

class ListImportacoes extends ListRecords
{
    protected static string $resource = ImportacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
