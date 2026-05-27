<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_estoque', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('peca_id')->index();

            // Tipo da movimentação
            $table->enum('tipo', ['entrada', 'saida', 'ajuste', 'cancelamento']);

            // Snapshot de quantidades
            $table->integer('quantidade_antes');
            $table->integer('quantidade_depois');
            $table->integer('quantidade_delta');  // + entrada/ajuste, − saida/cancelamento

            // Referência polimórfica manual (sem morphs do Eloquent para manter portabilidade)
            $table->string('referencia_tipo', 50);   // 'entrada_estoque' | 'ordem_servico' | 'ajuste_manual'
            $table->unsignedBigInteger('referencia_id');

            // Quem realizou
            $table->unsignedBigInteger('usuario_id')->nullable();

            // Append-only: sem updated_at
            $table->timestamp('created_at')->useCurrent();

            // FKs
            $table->foreign('peca_id')->references('id')->on('pecas')->onDelete('restrict');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('set null');

            // Índices
            $table->index(['tenant_id', 'peca_id']);
            $table->index(['tenant_id', 'referencia_tipo', 'referencia_id']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_estoque');
    }
};
