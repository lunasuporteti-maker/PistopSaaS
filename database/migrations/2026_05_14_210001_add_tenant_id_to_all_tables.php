<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Todas as tabelas que precisam de isolamento por tenant
    private array $tabelas = [
        'users',
        'clientes',
        'veiculos',
        'funcionarios',
        'parceiros',
        'pecas',
        'mao_de_obra',
        'catalogo_servicos',
        'tabela_servicos',
        'orcamentos',
        'ordens_servico',
        'agendamentos',
        'pagamentos_saida',
        'lembretes',
        'comissoes',
        'financeiro',
        'configuracoes',
    ];

    public function up(): void
    {
        foreach ($this->tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) use ($tabela) {
                // Adiciona tenant_id sem constraint FK (compatível com SQLite)
                // Em MySQL, o índice garante performance
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) use ($tabela) {
                $table->dropIndex([$tabela . '_tenant_id_index']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
