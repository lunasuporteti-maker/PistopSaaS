<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntradaEstoque;
use App\Services\EntradaEstoqueService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EntradaEstoqueController extends Controller
{
    public function __construct(private EntradaEstoqueService $service) {}

    // ─────────────────────────────────────────────────────────────
    // LIST
    // ─────────────────────────────────────────────────────────────

    /**
     * GET /api/entradas-estoque
     * Filtros: periodo_inicio, periodo_fim, fornecedor_id, status, peca_id
     */
    public function index(Request $request)
    {
        abort_if(auth()->user()->perfil === 'operador', 403, 'Acesso restrito.');

        $query = EntradaEstoque::with(['fornecedor', 'usuario'])
            ->orderBy('data_entrada', 'desc');

        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->fornecedor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('periodo_inicio')) {
            $query->where('data_entrada', '>=', Carbon::parse($request->periodo_inicio)->toDateString());
        }

        if ($request->filled('periodo_fim')) {
            $query->where('data_entrada', '<=', Carbon::parse($request->periodo_fim)->toDateString());
        }

        if ($request->filled('peca_id')) {
            $query->whereHas('itens', fn ($q) => $q->where('peca_id', $request->peca_id));
        }

        return response()->json($query->paginate(25));
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────

    /**
     * GET /api/entradas-estoque/{entrada}
     * Retorna entrada completa com itens, peças, fornecedor e usuário.
     */
    public function show(EntradaEstoque $entrada)
    {
        abort_if(auth()->user()->perfil === 'operador', 403, 'Acesso restrito.');

        return response()->json($entrada->load(['itens.peca', 'fornecedor', 'usuario']));
    }

    // ─────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────

    /**
     * POST /api/entradas-estoque
     * Cria uma entrada de estoque com itens e (opcionalmente) um anexo.
     */
    public function store(Request $request)
    {
        abort_if(auth()->user()->perfil === 'operador', 403, 'Acesso restrito.');

        $data = $request->validate([
            'fornecedor_id'                => 'required|integer|exists:fornecedores,id',
            'data_entrada'                 => 'nullable|date',
            'numero_nota'                  => 'nullable|string|max:100',
            'tipo_documento'               => 'nullable|in:nota_manual,cupom,nfe,sem_documento',
            'observacoes'                  => 'nullable|string',
            'itens'                        => 'required|array|min:1',
            'itens.*.peca_id'              => 'required|integer|exists:pecas,id',
            'itens.*.quantidade'           => 'required|integer|min:1',
            'itens.*.preco_custo_unitario' => 'required|numeric|min:0',
            'anexo'                        => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $anexo   = $request->hasFile('anexo') ? $request->file('anexo') : null;
        $entrada = $this->service->criar($data, $anexo);

        return response()->json($entrada, 201);
    }

    // ─────────────────────────────────────────────────────────────
    // CANCELAR
    // ─────────────────────────────────────────────────────────────

    /**
     * POST /api/entradas-estoque/{entrada}/cancelar
     * Cancela a entrada e reverte o estoque de cada item.
     */
    public function cancelar(Request $request, EntradaEstoque $entrada)
    {
        abort_if(auth()->user()->perfil === 'operador', 403, 'Acesso restrito.');

        $data = $request->validate([
            'motivo' => 'required|string|min:10|max:500',
        ]);

        $this->service->cancelar($entrada, $data['motivo'], auth()->id());

        return response()->json($entrada->fresh(['itens.peca', 'fornecedor']));
    }

    // ─────────────────────────────────────────────────────────────
    // EXPORT
    // ─────────────────────────────────────────────────────────────

    /**
     * GET /api/entradas-estoque/exportar
     * Gera CSV com as entradas do período (mesmos filtros do index).
     * Retorna response regular (não streamada) para compatibilidade com testes.
     */
    public function exportar(Request $request)
    {
        abort_if(auth()->user()->perfil === 'operador', 403, 'Acesso restrito.');

        $query = EntradaEstoque::with(['fornecedor'])
            ->orderBy('data_entrada', 'desc');

        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->fornecedor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('periodo_inicio')) {
            $query->where('data_entrada', '>=', Carbon::parse($request->periodo_inicio)->toDateString());
        }

        if ($request->filled('periodo_fim')) {
            $query->where('data_entrada', '<=', Carbon::parse($request->periodo_fim)->toDateString());
        }

        $entradas = $query->get();

        // Construir CSV em memória (dataset compatível com volumes esperados)
        $linhas = [];
        $linhas[] = implode(';', [
            'Nº Entrada', 'Data', 'Fornecedor', 'Nº Nota',
            'Tipo Documento', 'Valor Total', 'Status', 'Observações',
        ]);

        foreach ($entradas as $entrada) {
            $linhas[] = implode(';', [
                $entrada->numero_entrada,
                $entrada->data_entrada?->format('d/m/Y') ?? '',
                str_replace(';', ',', $entrada->fornecedor?->nome ?? '—'),
                $entrada->numero_nota ?? '',
                $entrada->tipo_documento,
                number_format((float) $entrada->valor_total, 2, ',', '.'),
                $entrada->status,
                str_replace([';', "\n", "\r"], [',', ' ', ' '], $entrada->observacoes ?? ''),
            ]);
        }

        // BOM UTF-8 para compatibilidade com Excel + conteúdo
        $conteudo     = "\xEF\xBB\xBF" . implode("\r\n", $linhas);
        $nomeArquivo  = 'entradas_estoque_' . now()->format('Y-m-d') . '.csv';

        return response($conteudo, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nomeArquivo}\"",
            'Pragma'              => 'no-cache',
        ]);
    }
}
