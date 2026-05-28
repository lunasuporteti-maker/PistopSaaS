<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->string('status', 30)->default('aprovado')->after('numero_os');
            $table->string('token_publico', 60)->nullable()->unique()->after('status');
            $table->text('andamento')->nullable()->after('token_publico');
            $table->timestamp('arquivado_em')->nullable()->after('andamento');
            $table->integer('posicao_fila')->default(0)->after('arquivado_em');
            $table->timestamp('aprovado_em')->nullable()->after('posicao_fila');
            $table->timestamp('iniciado_em')->nullable()->after('aprovado_em');
            $table->timestamp('concluido_em')->nullable()->after('iniciado_em');
            // finalizado_em já existe — renomear semanticamente para concluido seria migração destrutiva.
            // Mantemos finalizado_em para compatibilidade e passamos a usar concluido_em no kanban.
        });

        // Dados existentes: gera OS para orçamentos aprovados/em_servico sem OS ainda
        // Isso garante que o kanban do AutoFix não fique vazio após a mudança de fluxo.
        $orcamentos = DB::table('orcamentos')
            ->whereIn('status', ['aprovado', 'em_servico'])
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('ordens_servico')
                    ->whereColumn('ordens_servico.orcamento_id', 'orcamentos.id')
                    ->whereNull('ordens_servico.deleted_at');
            })
            ->get();

        foreach ($orcamentos as $orc) {
            $ultimo = DB::table('ordens_servico')->max('id') ?? 0;
            $numero = 'OS' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);

            DB::table('ordens_servico')->insert([
                'tenant_id'     => $orc->tenant_id,
                'numero_os'     => $numero,
                'orcamento_id'  => $orc->id,
                'cliente_id'    => $orc->cliente_id,
                'veiculo_id'    => $orc->veiculo_id,
                'descricao'     => $orc->queixa_cliente,
                'valor_total'   => $orc->valor_total,
                'status'        => $orc->status === 'em_servico' ? 'em_servico' : 'aprovado',
                'token_publico' => $orc->token_publico ?? Str::random(48),
                'aprovado_em'   => $orc->aprovado_em,
                'iniciado_em'   => $orc->status === 'em_servico' ? ($orc->iniciado_em ?? now()) : null,
                'posicao_fila'  => 0,
                'finalizado_em' => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropColumn(['status', 'token_publico', 'andamento', 'arquivado_em',
                'posicao_fila', 'aprovado_em', 'iniciado_em', 'concluido_em']);
        });
    }
};
