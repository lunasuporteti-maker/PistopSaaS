<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KanbanController extends Controller
{
    private array $colunas = [
        'aprovado'   => ['label' => 'Aprovado',    'cor' => '#17a2b8'],
        'em_servico' => ['label' => 'Em Serviço',  'cor' => '#e67e22'],
        'concluido'  => ['label' => 'Concluído',   'cor' => '#28a745'],
    ];

    private function mensagens(): array
    {
        $nome = app('tenant')->nome ?? 'Oficina';
        return [
            'aprovado'   => "Ola {nome}! Otima noticia! O servico do seu *{veiculo}* foi *aprovado*. Vamos iniciar em breve.\n\nAcompanhe em tempo real:\n{link}\n\n_{$nome}_",
            'em_servico' => "Ola {nome}! Seu *{veiculo}* esta na oficina e o servico esta *em andamento* aqui na *{$nome}*.\n\nAcompanhe:\n{link}\n\n_{$nome}_",
            'concluido'  => "Ola {nome}! Seu *{veiculo}* esta *pronto* e pode ser retirado na *{$nome}*! Foi um prazer atende-lo. Ate a proxima!\n\n_{$nome}_",
        ];
    }

    public function index()
    {
        $cards = OrdemServico::with(['orcamento.cliente', 'orcamento.veiculo'])
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

    public function estado()
    {
        $estado = OrdemServico::whereIn('status', array_keys($this->colunas))
            ->whereNull('arquivado_em')
            ->orderBy('updated_at', 'desc')
            ->pluck('updated_at', 'id');

        return response()->json([
            'hash'  => md5($estado->toJson()),
            'total' => $estado->count(),
        ]);
    }

    public function updateStatus(Request $request, OrdemServico $os)
    {
        $request->validate([
            'status' => 'required|in:aprovado,em_servico,concluido,cancelado',
        ]);

        if ($request->status === 'concluido') {
            return response()->json(['ok' => false, 'msg' => 'Use o fluxo de pagamento para concluir.'], 422);
        }

        $campos = ['status' => $request->status];

        match ($request->status) {
            'em_servico' => $campos['iniciado_em'] = now(),
            default      => null,
        };

        $os->update($campos);

        return response()->json([
            'ok'     => true,
            'status' => $request->status,
            'token'  => $os->token_publico,
        ]);
    }

    public function concluirComPagamento(Request $request, OrdemServico $os)
    {
        if (! in_array($os->status, ['aprovado', 'em_servico'])) {
            return response()->json(['ok' => false, 'msg' => 'OS deve estar aprovada ou em serviço.'], 422);
        }

        $request->validate([
            'pagamentos'         => 'required|array|min:1',
            'pagamentos.*.forma' => 'required|string',
            'pagamentos.*.valor' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($os, $request) {
            foreach ($request->pagamentos as $pag) {
                $os->pagamentos()->create(['forma' => $pag['forma'], 'valor' => $pag['valor']]);
            }

            $os->update([
                'status'        => 'concluido',
                'concluido_em'  => now(),
                'finalizado_em' => now(),
            ]);

            // Decrementa estoque das peças do orçamento (se ainda não foi feito)
            if ($os->pecas()->doesntExist() && $os->orcamento) {
                foreach ($os->orcamento->pecas()->with('peca')->get() as $item) {
                    $item->peca->decrement('quantidade', $item->quantidade);
                    $os->pecas()->create([
                        'peca_id'        => $item->peca_id,
                        'quantidade'     => $item->quantidade,
                        'preco_unitario' => $item->preco_unitario,
                    ]);
                }
            }
        });

        $orc      = $os->orcamento;
        $cliente  = $orc?->cliente;
        $veiculo  = $orc?->veiculo;
        $telefone = preg_replace('/\D/', '', $cliente?->telefone ?? '');
        $nomeVeic = trim(($veiculo?->marca ?? '') . ' ' . ($veiculo?->modelo ?? ''));
        $valor    = 'R$ ' . number_format($os->valor_total, 2, ',', '.');
        $nomeTenant = app('tenant')->nome ?? 'Oficina';

        $waMsg = "Ola {$cliente?->nome}! O servico do seu {$nomeVeic} foi concluido aqui na {$nomeTenant}.\n"
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

    public function registrarAndamento(Request $request, OrdemServico $os)
    {
        $request->validate(['andamento' => 'nullable|string|max:2000']);
        $os->update(['andamento' => $request->andamento]);
        return response()->json(['ok' => true]);
    }

    public function arquivar(OrdemServico $os)
    {
        if ($os->status !== 'concluido') {
            return response()->json(['ok' => false, 'msg' => 'Só é possível arquivar OSs concluídas.'], 422);
        }
        $os->update(['arquivado_em' => now()]);
        return response()->json(['ok' => true]);
    }
}
