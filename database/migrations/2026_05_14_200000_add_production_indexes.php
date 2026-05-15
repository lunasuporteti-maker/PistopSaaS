<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índice para busca de OS por data de finalização
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->index('finalizado_em');
            $table->index('cliente_id');
            $table->index('veiculo_id');
        });

        // Índice para buscas de orçamentos
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->index('cliente_id');
            $table->index('veiculo_id');
        });

        // Índice para pagamentos por data de criação (usado em relatórios)
        Schema::table('pagamentos_os', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('os_id');
        });

        // Índice para financeiro
        Schema::table('financeiro', function (Blueprint $table) {
            $table->index('os_id');
        });

        // Índice para lembretes por cliente
        Schema::table('lembretes', function (Blueprint $table) {
            $table->index('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropIndex(['finalizado_em']);
            $table->dropIndex(['cliente_id']);
            $table->dropIndex(['veiculo_id']);
        });

        Schema::table('orcamentos', function (Blueprint $table) {
            $table->dropIndex(['cliente_id']);
            $table->dropIndex(['veiculo_id']);
        });

        Schema::table('pagamentos_os', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['os_id']);
        });

        Schema::table('financeiro', function (Blueprint $table) {
            $table->dropIndex(['os_id']);
        });

        Schema::table('lembretes', function (Blueprint $table) {
            $table->dropIndex(['cliente_id']);
        });
    }
};
