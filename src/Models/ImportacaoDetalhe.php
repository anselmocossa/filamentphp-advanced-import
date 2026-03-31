<?php

namespace Filament\AdvancedImport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportacaoDetalhe extends Model
{
    protected $fillable = [
        'importacao_id',
        'status',
        'payload',
        'erro',
        'tempo_ms',
    ];

    protected $casts = [
        'payload' => 'array',
        'tempo_ms' => 'float',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('advanced-import.tables.importacao_detalhes', 'importacao_detalhes'));
    }

    /**
     * Get the parent import.
     */
    public function importacao(): BelongsTo
    {
        return $this->belongsTo(Importacao::class, 'importacao_id');
    }

    /**
     * Check if the detail represents a successful import.
     */
    public function isSuccess(): bool
    {
        return in_array($this->status, ['sucesso', 'success', 'lida', 'criado', 'atualizado']);
    }

    /**
     * Check if the detail represents a failed import.
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['falha', 'failed', 'rejeitado', 'erro']);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter successful records.
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['sucesso', 'success', 'lida', 'criado', 'atualizado']);
    }

    /**
     * Scope to filter failed records.
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['falha', 'failed', 'rejeitado', 'erro']);
    }
}
