<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('advanced-import.tables.importacao_detalhes', 'importacao_detalhes');
        $importacoesTable = config('advanced-import.tables.importacoes', 'importacoes');

        Schema::create($tableName, function (Blueprint $table) use ($importacoesTable) {
            $table->id();
            $table->foreignId('importacao_id')->constrained($importacoesTable)->cascadeOnDelete();
            $table->string('status')->default('pendente');
            $table->json('payload')->nullable();
            $table->text('erro')->nullable();
            $table->float('tempo_ms')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('importacao_id');
        });
    }

    public function down(): void
    {
        $tableName = config('advanced-import.tables.importacao_detalhes', 'importacao_detalhes');

        Schema::dropIfExists($tableName);
    }
};
