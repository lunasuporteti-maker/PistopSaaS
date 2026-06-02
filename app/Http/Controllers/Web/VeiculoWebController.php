<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Veiculo;
use App\Models\Cliente;
use App\Models\HistoricoKm;
use Illuminate\Http\Request;

class VeiculoWebController extends Controller
{
    public function index(Request $request)
    {
        $veiculos = Veiculo::with('cliente')
            ->when($request->search, fn($q) =>
                $q->where('placa', 'like', "%{$request->search}%")
                  ->orWhere('modelo', 'like', "%{$request->search}%")
                  ->orWhereHas('cliente', fn($c) => $c->where('nome', 'like', "%{$request->search}%"))
            )
            ->when($request->tipo, fn($q) => $q->where('tipo_veiculo', $request->tipo))
            ->orderBy('id', 'desc')->paginate(20);

        return view('pitstop.veiculos.index', compact('veiculos'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('pitstop.veiculos.form', ['veiculo' => new Veiculo, 'clientes' => $clientes]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'    => 'required|exists:clientes,id',
            'tipo_veiculo'  => 'required|in:carro,moto,caminhao,van,outro',
            'marca'         => 'required|string|max:60',
            'modelo'        => 'required|string|max:60',
            'ano'           => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'placa'         => 'required|string|max:7|unique:veiculos,placa|regex:/^[A-Z0-9]{7}$/',
            'cor'           => 'nullable|string|max:40',
            'km_atual'      => 'nullable|integer|min:0|max:9999999',
        ], [
            'placa.regex' => 'Placa inválida. Use o formato ABC1234 ou ABC1D23 (7 caracteres, sem traço).',
        ]);

        $data['placa']  = strtoupper($data['placa']);
        $data['modelo'] = strtoupper($data['modelo']);
        $data['cor']    = $data['cor'] ? strtoupper($data['cor']) : null;

        $veiculo = Veiculo::create($data);

        if ($data['km_atual'] ?? null) {
            HistoricoKm::create(['veiculo_id' => $veiculo->id, 'km' => $data['km_atual'], 'observacao' => 'Cadastro inicial']);
        }

        return redirect()->route('veiculos.index')->with('success', 'Veículo cadastrado.');
    }

    public function show(Veiculo $veiculo)
    {
        $veiculo->load(['cliente', 'historicoKm', 'orcamentos', 'ordensServico']);
        return view('pitstop.veiculos.show', compact('veiculo'));
    }

    public function edit(Veiculo $veiculo)
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('pitstop.veiculos.form', compact('veiculo', 'clientes'));
    }

    public function update(Request $request, Veiculo $veiculo)
    {
        $data = $request->validate([
            'tipo_veiculo' => 'required|in:carro,moto,caminhao,van,outro',
            'marca'        => 'required|string|max:60',
            'modelo'       => 'required|string|max:60',
            'ano'          => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'placa'        => 'required|string|max:7|unique:veiculos,placa,' . $veiculo->id . '|regex:/^[A-Z0-9]{7}$/',
            'cor'          => 'nullable|string|max:40',
            'km_atual'     => 'nullable|integer|min:0|max:9999999',
        ], [
            'placa.regex' => 'Placa inválida. Use o formato ABC1234 ou ABC1D23 (7 caracteres, sem traço).',
        ]);

        $data['placa']  = strtoupper($data['placa']);
        $data['modelo'] = strtoupper($data['modelo']);
        $data['cor']    = $data['cor'] ? strtoupper($data['cor']) : null;

        if (isset($data['km_atual']) && $data['km_atual'] !== $veiculo->km_atual) {
            HistoricoKm::create(['veiculo_id' => $veiculo->id, 'km' => $data['km_atual']]);
        }

        $veiculo->update($data);
        return redirect()->route('veiculos.show', $veiculo)->with('success', 'Veículo atualizado.');
    }

    public function destroy(Veiculo $veiculo)
    {
        if ($veiculo->orcamentos()->exists()) {
            return back()->with('error', 'Veículo possui vínculos e não pode ser excluído.');
        }
        $veiculo->delete();
        return redirect()->route('veiculos.index')->with('success', 'Veículo excluído.');
    }
}
