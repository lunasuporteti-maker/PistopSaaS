<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;

class PortalController extends Controller
{
    public function acompanhar(string $numeroOs)
    {
        $os = OrdemServico::with(['cliente', 'veiculo'])
            ->where('numero_os', strtoupper($numeroOs))
            ->first();

        if (! $os) {
            return response()->json(['message' => 'OS não encontrada.'], 404);
        }

        return response()->json([
            'numero_os'     => $os->numero_os,
            'status'        => $os->orcamento?->status ?? ($os->finalizado_em ? 'concluido' : 'em_servico'),
            'veiculo'       => "{$os->veiculo->marca} {$os->veiculo->modelo} - {$os->veiculo->placa}",
            'criado_em'     => $os->created_at,
            'finalizado_em' => $os->finalizado_em,
            'garantia_dias' => $os->garantia_dias,
        ]);
    }
}
