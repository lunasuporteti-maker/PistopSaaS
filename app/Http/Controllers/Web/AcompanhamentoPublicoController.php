<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;

class AcompanhamentoPublicoController extends Controller
{
    private array $etapas = [
        'orcamento'  => ['icone' => '📋', 'titulo' => 'Orçamento em Análise',   'desc' => 'Estamos avaliando seu veículo e preparando o orçamento. Em breve você receberá o detalhamento.', 'cor' => '#6c757d'],
        'aprovado'   => ['icone' => '✅', 'titulo' => 'Orçamento Aprovado',     'desc' => 'Seu orçamento foi aprovado! Nossa equipe iniciará o serviço em breve.', 'cor' => '#17a2b8'],
        'em_servico' => ['icone' => '🔧', 'titulo' => 'Serviço em Andamento',   'desc' => 'Seu veículo está em nossa oficina e o serviço está sendo realizado com cuidado.', 'cor' => '#e67e22'],
        'concluido'  => ['icone' => '🎉', 'titulo' => 'Serviço Concluído!',     'desc' => 'Seu veículo está pronto! Pode vir buscá-lo na AutoFix. Obrigado pela preferência!', 'cor' => '#28a745'],
        'cancelado'  => ['icone' => '❌', 'titulo' => 'Serviço Cancelado',      'desc' => 'Infelizmente este serviço foi cancelado. Entre em contato conosco para mais informações.', 'cor' => '#dc3545'],
    ];

    public function show(string $token)
    {
        $orcamento = Orcamento::withoutGlobalScope('tenant')
            ->with(['cliente', 'veiculo', 'servicos'])
            ->where('token_publico', $token)
            ->firstOrFail();

        $etapa   = $this->etapas[$orcamento->status] ?? $this->etapas['orcamento'];
        $etapas  = $this->etapas;
        $ordem   = ['orcamento', 'aprovado', 'em_servico', 'concluido'];
        $posAtual = array_search($orcamento->status, $ordem);

        return response()
            ->view('pitstop.acompanhamento', compact('orcamento', 'etapa', 'etapas', 'ordem', 'posAtual'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
