<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use App\Models\OrcamentoServico;
use App\Models\Cliente;
use App\Models\Veiculo;
use App\Models\Peca;
use App\Models\MaoDeObra;
use App\Models\OrdemServico;
use App\Models\CatalogoServico;
use App\Models\Lembrete;
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

        $orcamento = Orcamento::create($data + ['status' => 'orcamento', 'valor_total' => 0]);
        return redirect()->route('orcamentos.show', $orcamento)->with('success', 'Orçamento criado.');
    }

    public function show(Orcamento $orcamento)
    {
        $orcamento->load(['cliente', 'veiculo', 'servicos', 'pecas.peca', 'maoDeObra.maoDeObra', 'ordemServico']);
        $pecas = Peca::orderBy('nome')->get();
        $maos  = MaoDeObra::where('ativo', true)->orderBy('nome')->get();
        return view('pitstop.orcamentos.show', compact('orcamento', 'pecas', 'maos'));
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

        return redirect()->route('ordens.index')->with('success', 'OS gerada com sucesso.');
    }

    public function addServico(Request $request, Orcamento $orcamento)
    {
        $data = $request->validate([
            'servico_nome' => 'required|string|max:200',
            'valor'        => 'required|numeric|min:0',
        ]);

        $orcamento->servicos()->create($data);
        $orcamento->update(['valor_total' => $orcamento->servicos()->sum('valor') + $orcamento->pecas()->sum(DB::raw('quantidade * preco_unitario'))]);

        return back()->with('success', 'Serviço adicionado.');
    }

    public function removeServico(Orcamento $orcamento, OrcamentoServico $servico)
    {
        abort_if($servico->orcamento_id !== $orcamento->id, 403);
        $servico->delete();
        $orcamento->update(['valor_total' => $orcamento->servicos()->sum('valor') + $orcamento->pecas()->sum(DB::raw('quantidade * preco_unitario'))]);

        return back()->with('success', 'Serviço removido.');
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
