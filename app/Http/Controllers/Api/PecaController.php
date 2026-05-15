<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Peca;
use Illuminate\Http\Request;

class PecaController extends Controller
{
    public function index(Request $request)
    {
        $query = Peca::query();

        if ($request->search) {
            $query->where('nome', 'like', "%{$request->search}%");
        }

        if ($request->estoque_baixo === 'true') {
            $query->whereColumn('quantidade', '<=', 'estoque_minimo');
        }

        return response()->json($query->orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'           => 'required|string|max:200',
            'quantidade'     => 'nullable|integer|min:0',
            'preco_custo'    => 'nullable|numeric|min:0',
            'preco_venda'    => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
        ]);

        return response()->json(Peca::create($data), 201);
    }

    public function update(Request $request, Peca $peca)
    {
        $data = $request->validate([
            'nome'           => 'sometimes|string|max:200',
            'quantidade'     => 'nullable|integer|min:0',
            'preco_custo'    => 'nullable|numeric|min:0',
            'preco_venda'    => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
        ]);

        $peca->update($data);
        return response()->json($peca);
    }

    public function destroy(Peca $peca)
    {
        if ($peca->osPecas()->exists() || $peca->orcamentoPecas()->exists()) {
            return response()->json([
                'message' => 'Peça está vinculada a orçamentos ou OS e não pode ser excluída.',
            ], 409);
        }

        $peca->delete();
        return response()->json(['message' => 'Peça excluída.']);
    }
}
