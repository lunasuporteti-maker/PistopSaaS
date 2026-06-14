<?php

namespace App\Services;

use App\Models\Agendamento;
use App\Models\Caixa;
use App\Models\Comissao;
use App\Models\Financeiro;
use App\Models\Lembrete;
use App\Models\Orcamento;
use App\Models\OrcamentoInteracao;
use App\Models\OrcamentoMaoDeObra;
use App\Models\OrcamentoPeca;
use App\Models\OrcamentoServico;
use App\Models\OrdemServico;
use App\Models\OsPeca;
use App\Models\PagamentoOs;
use App\Models\PagamentoSaida;
use App\Models\ServicoFoto;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Reseta a MOVIMENTAÇÃO operacional de uma oficina (tenant), preservando
 * os cadastros base (clientes, veículos, peças/estoque, serviços,
 * funcionários, fornecedores, configurações e usuários).
 *
 * Usado tanto pelo comando `pitstop:resetar-movimentacao` quanto pela
 * ação do painel admin (super admin).
 */
class ResetMovimentacaoService
{
    /** Retorna a contagem de cada tipo de movimentação que seria removida. */
    public function contar(Tenant $tenant): array
    {
        $tid    = $tenant->id;
        $orcIds = $this->orcamentoIds($tenant);
        $osIds  = $this->osIds($tenant);

        return [
            'orçamentos'        => $orcIds->count(),
            '↳ serviços'        => OrcamentoServico::whereIn('orcamento_id', $orcIds)->count(),
            '↳ peças orçadas'   => OrcamentoPeca::whereIn('orcamento_id', $orcIds)->count(),
            '↳ mão de obra'     => OrcamentoMaoDeObra::whereIn('orcamento_id', $orcIds)->count(),
            '↳ interações'      => OrcamentoInteracao::withoutGlobalScope('tenant')->where('tenant_id', $tid)->count(),
            'ordens de serviço' => $osIds->count(),
            '↳ peças da OS'     => OsPeca::whereIn('os_id', $osIds)->count(),
            '↳ pagamentos (OS)' => PagamentoOs::whereIn('os_id', $osIds)->count(),
            '↳ comissões'       => Comissao::withoutGlobalScope('tenant')->where('tenant_id', $tid)->count(),
            '↳ financeiro'      => Financeiro::withoutGlobalScope('tenant')->where('tenant_id', $tid)->count(),
            'fotos de serviço'  => ServicoFoto::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->count(),
            'lembretes'         => Lembrete::withoutGlobalScope('tenant')->where('tenant_id', $tid)->count(),
            'agendamentos'      => Agendamento::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->count(),
            'caixas'            => Caixa::withoutGlobalScope('tenant')->where('tenant_id', $tid)->count(),
            'saídas (despesas)' => PagamentoSaida::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->count(),
        ];
    }

    /**
     * Exporta um backup JSON e remove toda a movimentação do tenant em transação.
     *
     * @return array{total:int, backup:string, contagem:array}
     */
    public function executar(Tenant $tenant): array
    {
        $tid       = $tenant->id;
        $contagem  = $this->contar($tenant);
        $total     = array_sum($contagem);
        $orcIds    = $this->orcamentoIds($tenant);
        $osIds     = $this->osIds($tenant);

        $backupPath = $this->exportarBackup($tenant, $orcIds, $osIds);

        DB::transaction(function () use ($tid, $orcIds, $osIds) {
            // Filhos da OS
            Comissao::withoutGlobalScope('tenant')->where('tenant_id', $tid)->delete();
            Financeiro::withoutGlobalScope('tenant')->where('tenant_id', $tid)->delete();
            PagamentoOs::whereIn('os_id', $osIds)->delete();
            OsPeca::whereIn('os_id', $osIds)->delete();

            // Lembretes e fotos (referenciam OS/orçamento)
            Lembrete::withoutGlobalScope('tenant')->where('tenant_id', $tid)->delete();
            ServicoFoto::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->forceDelete();

            // Ordens de serviço
            OrdemServico::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->forceDelete();

            // Filhos do orçamento
            OrcamentoInteracao::withoutGlobalScope('tenant')->where('tenant_id', $tid)->delete();
            OrcamentoServico::whereIn('orcamento_id', $orcIds)->delete();
            OrcamentoPeca::whereIn('orcamento_id', $orcIds)->delete();
            OrcamentoMaoDeObra::whereIn('orcamento_id', $orcIds)->delete();

            // Orçamentos
            Orcamento::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->forceDelete();

            // Demais movimentações
            Agendamento::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->forceDelete();
            Caixa::withoutGlobalScope('tenant')->where('tenant_id', $tid)->delete();
            PagamentoSaida::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->forceDelete();
        });

        return ['total' => $total, 'backup' => $backupPath, 'contagem' => $contagem];
    }

    private function orcamentoIds(Tenant $tenant)
    {
        return Orcamento::withoutGlobalScope('tenant')->withTrashed()
            ->where('tenant_id', $tenant->id)->pluck('id');
    }

    private function osIds(Tenant $tenant)
    {
        return OrdemServico::withoutGlobalScope('tenant')->withTrashed()
            ->where('tenant_id', $tenant->id)->pluck('id');
    }

    private function exportarBackup(Tenant $tenant, $orcIds, $osIds): string
    {
        $tid = $tenant->id;

        $snapshot = [
            'gerado_em' => Carbon::now()->toIso8601String(),
            'tenant'    => ['id' => $tid, 'slug' => $tenant->slug, 'nome' => $tenant->nome],
            'dados'     => [
                'orcamentos'            => DB::table('orcamentos')->where('tenant_id', $tid)->get(),
                'orcamento_servicos'    => DB::table('orcamento_servicos')->whereIn('orcamento_id', $orcIds)->get(),
                'orcamento_pecas'       => DB::table('orcamento_pecas')->whereIn('orcamento_id', $orcIds)->get(),
                'orcamento_mao_de_obra' => DB::table('orcamento_mao_de_obra')->whereIn('orcamento_id', $orcIds)->get(),
                'orcamento_interacoes'  => DB::table('orcamento_interacoes')->where('tenant_id', $tid)->get(),
                'ordens_servico'        => DB::table('ordens_servico')->where('tenant_id', $tid)->get(),
                'os_pecas'              => DB::table('os_pecas')->whereIn('os_id', $osIds)->get(),
                'pagamentos_os'         => DB::table('pagamentos_os')->whereIn('os_id', $osIds)->get(),
                'comissoes'             => DB::table('comissoes')->where('tenant_id', $tid)->get(),
                'financeiro'            => DB::table('financeiro')->where('tenant_id', $tid)->get(),
                'servico_fotos'         => DB::table('servico_fotos')->where('tenant_id', $tid)->get(),
                'lembretes'             => DB::table('lembretes')->where('tenant_id', $tid)->get(),
                'agendamentos'          => DB::table('agendamentos')->where('tenant_id', $tid)->get(),
                'caixas'                => DB::table('caixas')->where('tenant_id', $tid)->get(),
                'pagamentos_saida'      => DB::table('pagamentos_saida')->where('tenant_id', $tid)->get(),
            ],
        ];

        $arquivo = 'resets/reset-'.$tenant->slug.'-'.Carbon::now()->format('Ymd-His').'.json';
        Storage::disk('local')->put($arquivo, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return Storage::disk('local')->path($arquivo);
    }
}
