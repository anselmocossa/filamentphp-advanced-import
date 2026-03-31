<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('advanced-import.tables.importacoes', 'importacoes');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('categoria');
            $table->string('tipo_operacao')->nullable();
            $table->integer('total')->default(0);
            $table->integer('sucesso')->default(0);
            $table->integer('falha')->default(0);
            $table->json('detalhes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('concluido')->default(false);
            $table->timestamps();

            $table->index('categoria');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        $tableName = config('advanced-import.tables.importacoes', 'importacoes');

        Schema::dropIfExists($tableName);
    }
};
