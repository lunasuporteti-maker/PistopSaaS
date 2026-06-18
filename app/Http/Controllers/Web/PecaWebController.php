<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\ChecksTrialLimits;
use App\Http\Controllers\Controller;
use App\Models\Peca;
use Illuminate\Http\Request;

class PecaWebController extends Controller
{
    use ChecksTrialLimits;
    public function index(Request $request)
    {
        $busca = trim((string) $request->search);

        $pecas = Peca::when($busca !== '', function ($q) use ($busca) {
                // Busca insensivel a maiusc/minusc (portavel SQLite + PostgreSQL),
                // procurando no nome E nas especificacoes (ex.: "peugeot" acha as pecas
                // cadastradas para esses carros).
                $termo = '%' . mb_strtolower($busca) . '%';
                $q->where(fn($s) => $s
                    ->whereRaw('LOWER(nome) LIKE ?', [$termo])
                    ->orWhereRaw('LOWER(especificacoes) LIKE ?', [$termo]));
            })
            ->when($request->estoque_baixo, fn($q) => $q->whereColumn('quantidade', '<=', 'estoque_minimo'))
            ->orderBy('nome')->paginate(20);

        return view('pitstop.pecas.index', compact('pecas'));
    }

    public function create()
    {
        return view('pitstop.pecas.form', ['peca' => new Peca]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->verificarLimiteTrial('pecas')) {
            return $redirect;
        }

        $data = $request->validate([
            'nome'           => 'required|string|max:200',
            'especificacoes' => 'nullable|string|max:2000',
            'quantidade'     => 'nullable|integer|min:0',
            'preco_custo'    => 'nullable|numeric|min:0',
            'preco_venda'    => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
        ]);

        Peca::create($data);
        return redirect()->route('pecas.index')->with('success', 'Peça cadastrada.');
    }

    public function show(Peca $peca)
    {
        return redirect()->route('pecas.edit', $peca);
    }

    public function edit(Peca $peca)
    {
        return view('pitstop.pecas.form', compact('peca'));
    }

    public function update(Request $request, Peca $peca)
    {
        $data = $request->validate([
            'nome'           => 'required|string|max:200',
            'especificacoes' => 'nullable|string|max:2000',
            'quantidade'     => 'nullable|integer|min:0',
            'preco_custo'    => 'nullable|numeric|min:0',
            'preco_venda'    => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
        ]);

        $peca->update($data);
        return redirect()->route('pecas.index')->with('success', 'Peça atualizada.');
    }

    public function destroy(Peca $peca)
    {
        if ($peca->osPecas()->exists()) {
            return back()->with('error', 'Peça está vinculada a OS.');
        }
        $peca->delete();
        return redirect()->route('pecas.index')->with('success', 'Peça excluída.');
    }
}
