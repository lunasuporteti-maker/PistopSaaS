<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona tenant_id à tabela pagamentos_os.
 *
 * A tabela foi criada sem isolamento de tenant, fazendo o dashboard e o caixa
 * (que consultam PagamentoOs diretamente) somarem receita de todos os tenants.
 * Esta migration adiciona a coluna, indexa e faz backfill derivando o tenant
 * da OS vinculada. Depois o model passa a usar o trait BelongsToTenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pagamentos_os', 'tenant_id')) {
            Schema::table('pagamentos_os', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            });
        }

        // Backfill: deriva tenant_id da OS vinculada.
        // Subquery correlacionada compatível com PostgreSQL (produção) e SQLite (testes).
        DB::table('pagamentos_os')->update([
            'tenant_id' => DB::raw('(SELECT tenant_id FROM ordens_servico WHERE ordens_servico.id = pagamentos_os.os_id)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('pagamentos_os', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
