<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntradaEstoque;
use App\Models\Fornecedor;
use App\Rules\CnpjValido;
use App\Services\FornecedorService;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    public function __construct(private FornecedorService $service) {}

    public function index(Request $request)
    {
        $query = Fornecedor::query();

        if ($request->filled('search')) {
            $termo = $request->search;
            $query->where(function ($q) use ($termo) {
                $q->where('nome', 'like', "%{$termo}%")
                  ->orWhere('cnpj', 'like', "%{$termo}%");
            });
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', filter_var($request->ativo, FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json($query->orderBy('nome')->paginate(25));
    }

    public function store(Request $request)
    {
        abort_if(auth()->user()->perfil === 'operador', 403, 'Acesso restrito.');

        $data = $request->validate([
            'nome'        => 'required|string|max:200',
            'cnpj'        => ['nullable', 'string', new CnpjValido()],
            'telefone'    => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:150',
            'endereco'    => 'nullable|string',
            'observacoes' => 'nullable|string',
            'ativo'       => 'nullable|boolean',
        ]);

        $fornecedor = $this->service->create($data);
        return response()->json($fornecedor, 201);
    }

    public function show(Fornecedor $fornecedor)
    {
        return response()->json($fornecedor);
    }

    public function update(Request $request, Fornecedor $fornecedor)
    {
        abort_if(auth()->user()->perfil === 'operador', 403, 'Acesso restrito.');

        $data = $request->validate([
            'nome'        => 'sometimes|string|max:200',
            'cnpj'        => ['nullable', 'string', new CnpjValido()],
            'telefone'    => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:150',
            'endereco'    => 'nullable|string',
            'observacoes' => 'nullable|string',
            'ativo'       => 'nullable|boolean',
        ]);

        return response()->json($this->service->update($fornecedor, $data));
    }

    public function destroy(Request $request, Fornecedor $fornecedor)
    {
        abort_if(auth()->user()->perfil === 'operador', 403, 'Acesso restrito.');

        if ($request->boolean('force')) {
            $this->service->forceDelete($fornecedor);
            return response()->json(['message' => 'Fornecedor excluído permanentemente.']);
        }

        $this->service->archive($fornecedor);
        return response()->json(['message' => 'Fornecedor arquivado com sucesso.']);
    }

    public function historicoCompras(Fornecedor $fornecedor)
    {
        abort_if(auth()->user()->perfil === 'operador', 403, 'Acesso restrito.');

        $historico = EntradaEstoque::where('fornecedor_id', $fornecedor->id)
            ->select('id', 'numero_entrada', 'data_entrada', 'valor_total', 'status')
            ->orderBy('data_entrada', 'desc')
            ->paginate(25);

        return response()->json($historico);
    }
}
