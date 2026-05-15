<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KanbanController extends Controller
{
    private array $colunas = [
        'orcamento'  => ['label' => 'Orçamento',   'cor' => '#6c757d'],
        'aprovado'   => ['label' => 'Aprovado',     'cor' => '#17a2b8'],
        'em_servico' => ['label' => 'Em Serviço',   'cor' => '#ffc107'],
        'concluido'  => ['label' => 'Concluído',    'cor' => '#28a745'],
        'cancelado'  => ['label' => 'Cancelado',    'cor' => '#dc3545'],
    ];

    private array $mensagens = [
        'orcamento'  => 'Olá {nome}! 👋 Recebemos seu veículo *{veiculo}* e estamos preparando o orçamento. Em breve retornamos com os detalhes!',
        'aprovado'   => 'Olá {nome}! ✅ Seu orçamento para o *{veiculo}* foi *aprovado*. Em breve iniciaremos o serviço. Qualquer dúvida, estamos à disposição!',
        'em_servico' => 'Olá {nome}! 🔧 O serviço no seu *{veiculo}* está em andamento. Assim que ficar pronto avisamos!',
        'concluido'  => 'Olá {nome}! 🎉 O serviço no seu *{veiculo}* foi *concluído com sucesso*! Pode vir buscar. Obrigado pela preferência!',
        'cancelado'  => 'Olá {nome}! Infelizmente seu orçamento para o *{veiculo}* foi cancelado. Entre em contato para mais informações.',
    ];

    public function index()
    {
        $cards = Orcamento::with(['cliente', 'veiculo'])
            ->whereIn('status', array_keys($this->colunas))
            ->orderBy('posicao_fila')
            ->orderBy('created_at')
            ->get()
            ->groupBy('status');

        $colunas   = $this->colunas;
        $mensagens = $this->mensagens;

        return view('pitstop.kanban', compact('cards', 'colunas', 'mensagens'));
    }

    public function updateStatus(Request $request, Orcamento $orcamento)
    {
        $request->validate([
            'status' => 'required|in:orcamento,aprovado,em_servico,concluido,cancelado',
        ]);

        $campos = ['status' => $request->status];

        match ($request->status) {
            'aprovado'   => $campos['aprovado_em']  = now(),
            'em_servico' => $campos['iniciado_em']  = now(),
            'concluido'  => $campos['concluido_em'] = now(),
            default      => null,
        };

        $orcamento->update($campos);

        return response()->json(['ok' => true, 'status' => $request->status]);
    }
}
