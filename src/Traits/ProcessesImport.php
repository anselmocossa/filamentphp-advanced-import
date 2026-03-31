<?php

namespace Filament\AdvancedImport\Traits;

use Exception;
use Filament\AdvancedImport\Models\Importacao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait ProcessesImport
{
    /**
     * Process an import batch within a database transaction.
     *
     * @param  array  $dados  Data to be imported
     * @param  string  $categoria  Import category
     * @param  string|null  $codigo  Unique import code (UUID generated if null)
     * @param  int|null  $userId  User ID for audit
     * @return array{codigo: string, status: string, total: int, sucesso: int, falha: int, dados: array}
     *
     * @throws Exception
     */
    protected function processImportBatch(
        array $dados,
        string $categoria,
        ?string $codigo = null,
        ?int $userId = null
    ): array {
        DB::beginTransaction();

        try {
            // Create or retrieve import record
            $importacao = Importacao::firstOrCreate(
                [
                    'codigo' => $codigo ?: (string) Str::uuid(),
                    'categoria' => $categoria,
                ],
                [
                    'total' => 0,
                    'sucesso' => 0,
                    'falha' => 0,
                    'detalhes' => [],
                    'user_id' => $userId ?? auth()->id(),
                ]
            );

            $detalhesExistentes = $importacao->detalhes ?? [];
            $novosDetalhes = [];
            $sucesso = 0;
            $falha = 0;

            foreach ($dados as $index => $item) {
                $startTime = microtime(true);

                try {
                    // Process individual item
                    $resultado = $this->processItem($item);

                    $resultado['estado'] = $resultado['estado'] ?? 'sucesso';
                    $novosDetalhes[] = $resultado;

                    // Create detail record
                    $importacao->detalhesRegistros()->create([
                        'status' => $resultado['estado'],
                        'payload' => $item,
                        'tempo_ms' => (microtime(true) - $startTime) * 1000,
                    ]);

                    $sucesso++;

                    $this->logSuccess($categoria, $index, $resultado);

                } catch (Exception $e) {
                    $this->logError($categoria, $index, $e);

                    $errorDetail = [
                        ...$item,
                        'estado' => 'rejeitado',
                        'error' => $e->getMessage(),
                    ];

                    $novosDetalhes[] = $errorDetail;

                    // Create detail record for failure
                    $importacao->detalhesRegistros()->create([
                        'status' => 'rejeitado',
                        'payload' => $item,
                        'erro' => $e->getMessage(),
                        'tempo_ms' => (microtime(true) - $startTime) * 1000,
                    ]);

                    $falha++;
                }
            }

            // Merge and update import record
            $detalhesCompletos = array_merge($detalhesExistentes, $novosDetalhes);

            $importacao->update([
                'total' => $importacao->total + count($dados),
                'sucesso' => $importacao->sucesso + $sucesso,
                'falha' => $importacao->falha + $falha,
                'detalhes' => $detalhesCompletos,
                'concluido' => true,
            ]);

            DB::commit();

            return [
                'codigo' => $importacao->codigo,
                'status' => 'concluido',
                'total' => $importacao->total,
                'sucesso' => $importacao->sucesso,
                'falha' => $importacao->falha,
                'dados' => $detalhesCompletos,
            ];

        } catch (Exception $e) {
            DB::rollBack();

            $this->logFatalError($categoria, $e);

            throw $e;
        }
    }

    /**
     * Process a single item.
     * This method must be implemented by the class using this trait.
     *
     * @param  array  $item  The item to process
     * @return array  Result with at least 'estado' key
     */
    abstract protected function processItem(array $item): array;

    /**
     * Log a successful import.
     */
    protected function logSuccess(string $categoria, int $index, array $resultado): void
    {
        if (! config('advanced-import.logging.log_success', true)) {
            return;
        }

        $channel = config('advanced-import.logging.channel');
        $logger = $channel ? Log::channel($channel) : Log::getFacadeRoot();

        $logger->info("Import [{$categoria}] Item {$index}: Success", [
            'categoria' => $categoria,
            'index' => $index,
            'resultado' => $resultado,
        ]);
    }

    /**
     * Log an import error.
     */
    protected function logError(string $categoria, int $index, Exception $e): void
    {
        if (! config('advanced-import.logging.log_errors', true)) {
            return;
        }

        $channel = config('advanced-import.logging.channel');
        $logger = $channel ? Log::channel($channel) : Log::getFacadeRoot();

        $logger->error("Import [{$categoria}] Item {$index}: {$e->getMessage()}", [
            'categoria' => $categoria,
            'index' => $index,
            'exception' => $e,
        ]);
    }

    /**
     * Log a fatal error during import.
     */
    protected function logFatalError(string $categoria, Exception $e): void
    {
        $channel = config('advanced-import.logging.channel');
        $logger = $channel ? Log::channel($channel) : Log::getFacadeRoot();

        $logger->error("Import [{$categoria}] Fatal Error: {$e->getMessage()}", [
            'categoria' => $categoria,
            'exception' => $e,
        ]);
    }
}
