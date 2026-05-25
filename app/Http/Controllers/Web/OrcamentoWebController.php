<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use App\Models\OrcamentoServico;
use App\Models\OrcamentoPeca;
use App\Models\OrcamentoMaoDeObra;
use App\Models\Cliente;
use App\Models\Veiculo;
use App\Models\Peca;
use App\Models\MaoDeObra;
use App\Models\OrdemServico;
use App\Models\CatalogoServico;
use App\Models\Lembrete;
use App\Models\HistoricoKm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrcamentoWebController extends Controller
{
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
        // Gera token público para o cliente acompanhar o andamento
        $token = $orcamento->token_publico ?? Str::random(48);
        $orcamento->update(['status' => 'aprovado', 'aprovado_em' => now(), 'token_publico' => $token]);
        return back()->with('success', 'Orçamento aprovado! Link de acompanhamento gerado para o cliente.');
    }

    public function gerarOs(Orcamento $orcamento)
    {
        if ($orcamento->ordemServico()->exists()) {
            if (request()->wantsJson()) {
                return response()->json(['ok' => false, 'msg' => 'OS já foi gerada para este orçamento.'], 422);
            }
            return back()->with('error', 'OS já foi gerada para este orçamento.');
        }

        DB::transaction(function () use ($orcamento) {
            $os = OrdemServico::create([
                'numero_os'    => OrdemServico::gerarNumeroOs(),
                'orcamento_id' => $orcamento->id,
                'cliente_id'   => $orcamento->cliente_id,
                'veiculo_id'   => $orcamento->veiculo_id,
                'descricao'    => $orcamento->queixa_cliente,
                'valor_total'  => $orcamento->valor_total,
            ]);

            foreach ($orcamento->pecas as $item) {
                $item->peca->decrement('quantidade', $item->quantidade);
                $os->pecas()->create([
                    'peca_id'        => $item->peca_id,
                    'quantidade'     => $item->quantidade,
                    'preco_unitario' => $item->preco_unitario,
                ]);
            }

            foreach ($orcamento->servicos as $servico) {
                $catalogo = CatalogoServico::where('nome', 'like', "%{$servico->servico_nome}%")
                    ->whereNotNull('dias_lembrete')->first();

                if ($catalogo) {
                    Lembrete::create([
                        'cliente_id'    => $orcamento->cliente_id,
                        'veiculo_id'    => $orcamento->veiculo_id,
                        'os_id'         => $os->id,
                        'servico_nome'  => $servico->servico_nome,
                        'data_servico'  => now(),
                        'data_lembrete' => now()->addDays($catalogo->dias_lembrete),
                    ]);
                }
            }

            $orcamento->update(['status' => 'em_servico', 'iniciado_em' => now()]);
        });

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
        $total = $orcamento->servicos()->sum('valor')
               + $orcamento->pecas()->sum(DB::raw('quantidade * preco_unitario'))
               + $orcamento->maoDeObra()->sum('valor');
        $orcamento->update(['valor_total' => $total]);
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
