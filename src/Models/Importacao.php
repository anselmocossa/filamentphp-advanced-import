<?php

namespace Filament\AdvancedImport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Importacao extends Model
{
    protected $fillable = [
        'codigo',
        'categoria',
        'tipo_operacao',
        'total',
        'sucesso',
        'falha',
        'detalhes',
        'user_id',
        'concluido',
    ];

    protected $casts = [
        'detalhes' => 'array',
        'concluido' => 'boolean',
        'total' => 'integer',
        'sucesso' => 'integer',
        'falha' => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('advanced-import.tables.importacoes', 'importacoes'));
    }

    /**
     * Get the user that created the import.
     */
    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');

        return $this->belongsTo($userModel);
    }

    /**
     * Get the import details.
     */
    public function detalhesRegistros(): HasMany
    {
        return $this->hasMany(ImportacaoDetalhe::class, 'importacao_id');
    }

    /**
     * Get success rate as percentage.
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total === 0) {
            return 0;
        }

        return round(($this->sucesso / $this->total) * 100, 2);
    }

    /**
     * Check if import has any failures.
     */
    public function hasFailures(): bool
    {
        return $this->falha > 0;
    }

    /**
     * Check if import is complete (all processed).
     */
    public function isComplete(): bool
    {
        return $this->concluido || ($this->total > 0 && ($this->sucesso + $this->falha) >= $this->total);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
