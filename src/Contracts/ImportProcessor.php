<?php

namespace Filament\AdvancedImport\Contracts;

interface ImportProcessor
{
    /**
     * Process the imported data.
     *
     * @param  array  $dados  Parsed data from Excel
     * @param  array  $context  Additional context (month, year, etc.)
     * @return array{codigo: string, total: int, sucesso: int, falha: int, dados: array}
     */
    public function process(array $dados, array $context = []): array;
}
