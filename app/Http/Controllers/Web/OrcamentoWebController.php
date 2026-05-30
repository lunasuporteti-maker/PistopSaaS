<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\ChecksTrialLimits;
use App\Http\Controllers\Controller;
use App\Jobs\NotificarRevisaoValorJob;
use App\Models\Orcamento;
use App\Models\OrcamentoInteracao;
use App\Models\OrcamentoServico;
use App\Models\OrcamentoPeca;
use App\Models\OrcamentoMaoDeObra;
use App\Models\Cliente;
use App\Models\Veiculo;
use App\Models\Peca;
use App\Models\MaoDeObra;
use App\Models\CatalogoServico;
use App\Models\HistoricoKm;
use App\Services\OrcamentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrcamentoWebController extends Controller
{
    use ChecksTrialLimits;
    public function index(Request $request)
    {
        $orcamentos = Orcamento::with(['cliente', 'veiculo'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('pitstop.orcamentos.index', compact('orcamentos'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();
        $pecas    = Peca::orderBy('nome')->get();
        $maos     = MaoDeObra::where('ativo', true)->orderBy('nome')->get();
        return view('pitstop.orcamentos.form', compact('clientes', 'pecas', 'maos', ) + ['orcamento' => new Orcamento]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->verificarLimiteTrial('orcamentos')) {
            return $redirect;
        }

        $data = $request->validate([
            'cliente_id'     => 'required|exists:clientes,id',
            'veiculo_id'     => 'required|exists:veiculos,id',
            'observacao'     => 'nullable|string',
            'km_entrada'     => 'nullable|integer',
            'queixa_cliente' => 'nullable|string',
        ]);

        $orcamento = Orcamento::create($data + ['status' => 'orcamento', 'valor_total' => 0, 'token_publico' => Str::random(48)]);

        // Registrar KM de entrada no histórico e atualizar km_atual do veículo
        if (!empty($data['km_entrada'])) {
            HistoricoKm::create([
                'veiculo_id' => $orcamento->veiculo_id,
                'km'         => $data['km_entrada'],
                'observacao' => 'Entrada — Orçamento #' . $orcamento->id,
            ]);

            $veiculo = $orcamento->veiculo;
            if (!$veiculo->km_atual || $data['km_entrada'] > $veiculo->km_atual) {
                $veiculo->update(['km_atual' => $data['km_entrada']]);
            }
        }

        return redirect()->route('orcamentos.show', $orcamento)->with('success', 'Orçamento criado.');
    }

    public function show(Orcamento $orcamento)
    {
        if (!$orcamento->token_publico) {
            $orcamento->update(['token_publico' => Str::random(48)]);
        }

        $orcamento->load(['cliente', 'veiculo', 'servicos', 'pecas.peca', 'maoDeObra.maoDeObra', 'ordemServico']);
        $pecas            = Peca::orderBy('nome')->get();
        $maos             = MaoDeObra::where('ativo', true)->orderBy('nome')->get();
        $catalogoServicos = CatalogoServico::where('ativo', true)->orderBy('nome')->get();
        return view('pitstop.orcamentos.show', compact('orcamento', 'pecas', 'maos', 'catalogoServicos'));
    }

    public function edit(Orcamento $orcamento)
    {
        $clientes = Cliente::orderBy('nome')->get();
        $pecas    = Peca::orderBy('nome')->get();
        $maos     = MaoDeObra::where('ativo', true)->orderBy('nome')->get();
        return view('pitstop.orcamentos.form', compact('orcamento', 'clientes', 'pecas', 'maos'));
    }

    public function update(Request $request, Orcamento $orcamento)
    {
        $data = $request->validate([
            'observacao'      => 'nullable|string',
            'km_entrada'      => 'nullable|integer',
            'queixa_cliente'  => 'nullable|string',
            'parecer_tecnico' => 'nullable|string',
        ]);

        $orcamento->update($data);
        return redirect()->route('orcamentos.show', $orcamento)->with('success', 'Orçamento atualizado.');
    }

    public function aprovar(Orcamento $orcamento)
    {
        $token = $orcamento->token_publico ?? Str::random(48);
        $orcamento->update(['status' => 'aprovado', 'aprovado_em' => now(), 'token_publico' => $token]);

        // Gera OS automaticamente ao aprovar — ela navega o Kanban a partir daqui
        app(OrcamentoService::class)->gerarOs($orcamento->fresh());

        return back()->with('success', 'Orçamento aprovado! OS gerada e disponível no Kanban.');
    }

    public function gerarOs(Orcamento $orcamento)
    {
        try {
            $os = app(OrcamentoService::class)->gerarOs($orcamento);
            $orcamento->update(['status' => 'em_servico', 'iniciado_em' => now()]);
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['ok' => false, 'msg' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->route('ordens.index')->with('success', 'OS gerada com sucesso.');
    }

    public function addServico(Request $request, Orcamento $orcamento)
    {
        $data = $request->validate([
            'servico_nome' => 'required|string|max:200',
            'valor'        => 'required|numeric|min:0',
        ]);

        $orcamento->servicos()->create($data);
        $this->recalcularTotal($orcamento);

        return back()->with('success', 'Serviço adicionado.');
    }

    public function removeServico(Orcamento $orcamento, OrcamentoServico $servico)
    {
        abort_if($servico->orcamento_id !== $orcamento->id, 403);
        $servico->delete();
        $this->recalcularTotal($orcamento);

        return back()->with('success', 'Serviço removido.');
    }

    public function addPeca(Request $request, Orcamento $orcamento)
    {
        $data = $request->validate([
            'peca_id'        => 'required|exists:pecas,id',
            'quantidade'     => 'required|integer|min:1',
            'preco_unitario' => 'required|numeric|min:0',
        ]);

        $orcamento->pecas()->create($data);
        $this->recalcularTotal($orcamento);

        return back()->with('success', 'Peça adicionada.');
    }

    public function removePeca(Orcamento $orcamento, OrcamentoPeca $peca)
    {
        abort_if($peca->orcamento_id !== $orcamento->id, 403);
        $peca->delete();
        $this->recalcularTotal($orcamento);

        return back()->with('success', 'Peça removida.');
    }

    public function addMaoDeObra(Request $request, Orcamento $orcamento)
    {
        $data = $request->validate([
            'mao_de_obra_id' => 'nullable|exists:mao_de_obra,id',
            'nome_custom'    => 'nullable|string|max:200',
            'valor'          => 'required|numeric|min:0',
        ]);

        $orcamento->maoDeObra()->create($data);
        $this->recalcularTotal($orcamento);

        return back()->with('success', 'Mão de obra adicionada.');
    }

    public function removeMaoDeObra(Orcamento $orcamento, OrcamentoMaoDeObra $maoDeObra)
    {
        abort_if($maoDeObra->orcamento_id !== $orcamento->id, 403);
        $maoDeObra->delete();
        $this->recalcularTotal($orcamento);

        return back()->with('success', 'Mão de obra removida.');
    }

    private function recalcularTotal(Orcamento $orcamento): void
    {
        $valorAntes = (float) $orcamento->valor_total;

        $total = $orcamento->servicos()->sum('valor')
               + $orcamento->pecas()->sum(DB::raw('quantidade * preco_unitario'))
               + $orcamento->maoDeObra()->sum('valor');

        $orcamento->update(['valor_total' => $total]);

        // FR-010: se valor mudou após aprovação → reverte para re-aprovação
        if ($orcamento->status === 'aprovado' && abs($total - $valorAntes) > 0.001) {
            $orcamento->update([
                'status'               => 'orcamento',
                'aprovado_em'          => null,
                'aprovado_por_canal'   => null,
                'aprovado_ip'          => null,
                'aprovado_user_agent'  => null,
            ]);

            OrcamentoInteracao::create([
                'tenant_id'    => $orcamento->tenant_id,
                'orcamento_id' => $orcamento->id,
                'tipo'         => OrcamentoInteracao::TIPO_REVISAO_VALOR,
                'dados_json'   => [
                    'valor_antes' => $valorAntes,
                    'valor_novo'  => $total,
                    'delta'       => round($total - $valorAntes, 2),
                ],
                'usuario_id'   => auth()->id(),
            ]);

            NotificarRevisaoValorJob::dispatch($orcamento->id, $valorAntes, $total);

            // Invalida a OS gerada (se existir) pois o orçamento voltou ao início
            $orcamento->ordemServico()->update(['status' => 'cancelado']);
        }
    }

    public function destroy(Orcamento $orcamento)
    {
        if ($orcamento->ordemServico()->exists()) {
            return back()->with('error', 'Orçamento possui OS vinculada.');
        }

        $orcamento->delete();
        return redirect()->route('orcamentos.index')->with('success', 'Orçamento excluído.');
    }
}
