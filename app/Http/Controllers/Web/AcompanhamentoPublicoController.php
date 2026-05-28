<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use App\Models\OrdemServico;

class AcompanhamentoPublicoController extends Controller
{
    private array $etapas = [
        'orcamento'  => ['icone' => '📋', 'titulo' => 'Orçamento em Análise',   'desc' => 'Estamos avaliando seu veículo e preparando o orçamento.', 'cor' => '#6c757d'],
        'aprovado'   => ['icone' => '✅', 'titulo' => 'Aprovado — Aguardando Início', 'desc' => 'Orçamento aprovado! Nossa equipe iniciará o serviço em breve.', 'cor' => '#17a2b8'],
        'em_servico' => ['icone' => '🔧', 'titulo' => 'Serviço em Andamento',   'desc' => 'Seu veículo está em nossa oficina e o serviço está sendo realizado.', 'cor' => '#e67e22'],
        'concluido'  => ['icone' => '🎉', 'titulo' => 'Serviço Concluído!',     'desc' => 'Seu veículo está pronto! Pode vir buscá-lo. Obrigado pela preferência!', 'cor' => '#28a745'],
        'cancelado'  => ['icone' => '❌', 'titulo' => 'Serviço Cancelado',      'desc' => 'Este serviço foi cancelado. Entre em contato para mais informações.', 'cor' => '#dc3545'],
    ];

    public function show(string $token)
    {
        // Busca OS pelo token primeiro (novo fluxo)
        $os = OrdemServico::withoutGlobalScope('tenant')
            ->with(['orcamento.cliente', 'orcamento.veiculo', 'orcamento.servicos'])
            ->where('token_publico', $token)
            ->first();

        if ($os) {
            $orcamento = $os->orcamento;
            $status    = $os->status;
        } else {
            // Fallback: token antigo em orcamentos (legado AutoFix)
            $orcamento = Orcamento::withoutGlobalScope('tenant')
                ->with(['cliente', 'veiculo', 'servicos'])
                ->where('token_publico', $token)
                ->firstOrFail();
            $status = $orcamento->status;
        }

        $etapa    = $this->etapas[$status] ?? $this->etapas['orcamento'];
        $etapas   = $this->etapas;
        $ordem    = ['orcamento', 'aprovado', 'em_servico', 'concluido'];
        $posAtual = array_search($status, $ordem);

        return response()
            ->view('pitstop.acompanhamento', compact('orcamento', 'etapa', 'etapas', 'ordem', 'posAtual'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
