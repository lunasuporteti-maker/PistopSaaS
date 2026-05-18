<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KanbanController extends Controller
{
    private array $colunas = [
        'orcamento'  => ['label' => 'Orcamento',    'cor' => '#6c757d'],
        'aprovado'   => ['label' => 'Aprovado',      'cor' => '#17a2b8'],
        'em_servico' => ['label' => 'Em Servico',    'cor' => '#e67e22'],
        'concluido'  => ['label' => 'Concluido',     'cor' => '#28a745'],
        'cancelado'  => ['label' => 'Cancelado',     'cor' => '#dc3545'],
    ];

    // Mensagens sem emojis para evitar problemas de encoding no WhatsApp URL
    // Os placeholders {link} e {nome} e {veiculo} são substituídos na view
    private array $mensagens = [
        'orcamento'  => "Ola {nome}! Recebemos seu *{veiculo}* aqui na *AutoFix*. Ja estamos avaliando e em breve te enviamos o orcamento completo.\n\n_IAQueAtende - Sistema AutoFix_",
        'aprovado'   => "Ola {nome}! Otima noticia! Seu orcamento para o *{veiculo}* foi *aprovado*. Vamos iniciar o servico em breve.\n\nAcompanhe o andamento em tempo real:\n{link}\n\n_IAQueAtende - Sistema AutoFix_",
        'em_servico' => "Ola {nome}! Seu *{veiculo}* ja esta na oficina e o servico esta em *andamento* aqui na *AutoFix*.\n\nAcompanhe o status em tempo real pelo link abaixo:\n{link}\n\nAssim que ficar pronto te avisamos!\n\n_IAQueAtende - Sistema AutoFix_",
        'concluido'  => "Ola {nome}! Seu *{veiculo}* esta *pronto* e pode ser retirado na *AutoFix*! Foi um prazer atende-lo. Ate a proxima!\n\n_IAQueAtende - Sistema AutoFix_",
        'cancelado'  => "Ola {nome}, infelizmente nao foi possivel prosseguir com o servico do seu *{veiculo}* no momento. Entre em contato para mais informacoes. - *AutoFix*",
    ];

    public function index()
    {
        $cards = Orcamento::with(['cliente', 'veiculo', 'ordemServico'])
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

    // Endpoint leve para o auto-refresh verificar mudancas
    public function estado()
    {
        $estado = Orcamento::whereIn('status', array_keys($this->colunas))
            ->whereNull('arquivado_em')
            ->orderBy('updated_at', 'desc')
            ->pluck('updated_at', 'id');

        return response()->json([
            'hash'  => md5($estado->toJson()),
            'total' => $estado->count(),
        ]);
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

        // Gera token publico ao aprovar ou iniciar servico (para link de acompanhamento)
        if (in_array($request->status, ['aprovado', 'em_servico']) && ! $orcamento->token_publico) {
            $campos['token_publico'] = Str::random(48);
        }

        $orcamento->update($campos);

        return response()->json([
            'ok'     => true,
            'status' => $request->status,
            'token'  => $orcamento->fresh()->token_publico,
        ]);
    }

    public function arquivar(Orcamento $orcamento)
    {
        if ($orcamento->status !== 'concluido') {
            return response()->json(['ok' => false, 'msg' => 'So e possivel arquivar orcamentos concluidos.'], 422);
        }

        $orcamento->update(['arquivado_em' => now()]);

        return response()->json(['ok' => true]);
    }
}
