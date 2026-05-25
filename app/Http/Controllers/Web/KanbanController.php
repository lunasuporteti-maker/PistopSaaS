<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use App\Services\OrcamentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    // Nome da oficina resolvido dinamicamente via tenant para suportar multi-tenant
    private function mensagens(): array
    {
        $nome = app('tenant')->nome ?? 'Oficina';
        return [
            'orcamento'  => "Ola {nome}! Recebemos seu *{veiculo}* aqui na *{$nome}*. Ja estamos avaliando e em breve te enviamos o orcamento completo.\n\n_{$nome}_",
            'aprovado'   => "Ola {nome}! Otima noticia! Seu orcamento para o *{veiculo}* foi *aprovado*. Vamos iniciar o servico em breve.\n\nAcompanhe o andamento em tempo real:\n{link}\n\n_{$nome}_",
            'em_servico' => "Ola {nome}! Seu *{veiculo}* ja esta na oficina e o servico esta em *andamento* aqui na *{$nome}*.\n\nAcompanhe o status em tempo real pelo link abaixo:\n{link}\n\nAssim que ficar pronto te avisamos!\n\n_{$nome}_",
            'concluido'  => "Ola {nome}! Seu *{veiculo}* esta *pronto* e pode ser retirado na *{$nome}*! Foi um prazer atende-lo. Ate a proxima!\n\n_{$nome}_",
            'cancelado'  => "Ola {nome}, infelizmente nao foi possivel prosseguir com o servico do seu *{veiculo}* no momento. Entre em contato para mais informacoes. - *{$nome}*",
        ];
    }

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
        $mensagens = $this->mensagens();

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

        // Concluído só pode ser acionado via concluirComPagamento (com pagamento + OS)
        if ($request->status === 'concluido') {
            return response()->json(['ok' => false, 'msg' => 'Use o fluxo de pagamento para concluir.'], 422);
        }

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

    public function concluirComPagamento(Request $request, Orcamento $orcamento)
    {
        if ($orcamento->ordemServico()->exists()) {
            return response()->json(['ok' => false, 'msg' => 'OS já gerada para este orçamento.'], 422);
        }

        if (!in_array($orcamento->status, ['aprovado', 'em_servico'])) {
            return response()->json(['ok' => false, 'msg' => 'Orçamento deve estar aprovado ou em serviço.'], 422);
        }

        $request->validate([
            'pagamentos'         => 'required|array|min:1',
            'pagamentos.*.forma' => 'required|string',
            'pagamentos.*.valor' => 'required|numeric|min:0.01',
        ]);

        $os = null;

        DB::transaction(function () use ($orcamento, $request, &$os) {
            $os = app(OrcamentoService::class)->gerarOs($orcamento);

            // Registrar pagamentos (responsabilidade exclusiva do fluxo Kanban)
            foreach ($request->pagamentos as $pag) {
                $os->pagamentos()->create(['forma' => $pag['forma'], 'valor' => $pag['valor']]);
            }

            $orcamento->update(['status' => 'concluido', 'concluido_em' => now()]);
        });

        $cliente  = $orcamento->cliente;
        $veiculo  = $orcamento->veiculo;
        $telefone = preg_replace('/\D/', '', $cliente->telefone ?? '');
        $nomeVeic = trim(($veiculo->marca ?? '') . ' ' . ($veiculo->modelo ?? ''));
        $valor    = 'R$ ' . number_format($os->valor_total, 2, ',', '.');

        $nomeTenant = app('tenant')->nome ?? 'Oficina';
        $waMsg = "Ola {$cliente->nome}! O servico do seu {$nomeVeic} foi concluido aqui na {$nomeTenant}.\n"
               . "OS: {$os->numero_os} — Total: {$valor}\n"
               . "Aguardamos voce para retirada. Obrigado!";
        $waUrl = $telefone ? 'https://wa.me/55' . $telefone . '?text=' . rawurlencode($waMsg) : null;

        return response()->json([
            'ok'        => true,
            'os_id'     => $os->id,
            'numero_os' => $os->numero_os,
            'pdf_url'   => route('ordens.pdf', $os),
            'wa_url'    => $waUrl,
        ]);
    }

    public function registrarAndamento(Request $request, Orcamento $orcamento)
    {
        $request->validate(['andamento' => 'nullable|string|max:2000']);
        $orcamento->update(['andamento' => $request->andamento]);
        return response()->json(['ok' => true]);
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
