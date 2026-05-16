<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    private array $colunas = [
        'orcamento'  => ['label' => 'Orçamento',   'cor' => '#6c757d'],
        'aprovado'   => ['label' => 'Aprovado',     'cor' => '#17a2b8'],
        'em_servico' => ['label' => 'Em Serviço',   'cor' => '#e67e22'],
        'concluido'  => ['label' => 'Concluído',    'cor' => '#28a745'],
        'cancelado'  => ['label' => 'Cancelado',    'cor' => '#dc3545'],
    ];

    private array $mensagens = [
        'orcamento'  => "Olá {nome}! 👋 Recebemos seu *{veiculo}* aqui na *AutoFix*. Já estamos avaliando e em breve te enviamos o orçamento completo. 🔧\n_IAQueAtende — Sistema AutoFix_",
        'aprovado'   => "Olá {nome}! ✅ Ótima notícia! Seu orçamento para o *{veiculo}* foi *aprovado*. Vamos iniciar o serviço em breve. Qualquer dúvida, estamos à disposição! 😊\n_IAQueAtende — Sistema AutoFix_",
        'em_servico' => "Olá {nome}! 🔧 Seu *{veiculo}* já está na oficina e o serviço está em *andamento* aqui na *AutoFix*. Assim que ficar pronto te avisamos!\n_IAQueAtende — Sistema AutoFix_",
        'concluido'  => "Olá {nome}! 🎉 Seu *{veiculo}* está *pronto* e pode ser retirado na *AutoFix*! Foi um prazer atendê-lo. Até a próxima! ⭐\n_IAQueAtende — Sistema AutoFix_",
        'cancelado'  => "Olá {nome}, infelizmente não foi possível prosseguir com o serviço do seu *{veiculo}* no momento. Entre em contato para mais informações. — *AutoFix*",
    ];

    public function index()
    {
        $cards = Orcamento::with(['cliente', 'veiculo'])
            ->whereIn('status', array_keys($this->colunas))
            ->whereNull('arquivado_em')
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

    public function arquivar(Orcamento $orcamento)
    {
        if ($orcamento->status !== 'concluido') {
            return response()->json(['ok' => false, 'msg' => 'Só é possível arquivar orçamentos concluídos.'], 422);
        }

        $orcamento->update(['arquivado_em' => now()]);

        return response()->json(['ok' => true]);
    }
}
