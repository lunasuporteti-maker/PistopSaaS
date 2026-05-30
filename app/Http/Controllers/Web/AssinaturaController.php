<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\Peca;
use App\Models\SubscriptionLog;
use App\Models\User;
use App\Services\AsaasService;
use App\Services\TrialLimitService;
use Illuminate\Support\Carbon;

class AssinaturaController extends Controller
{
    public function index()
    {
        $tenant  = app('tenant');
        $service = app(TrialLimitService::class);

        $logs = SubscriptionLog::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $uso = null;
        if ($service->tenantEmTrial($tenant)) {
            $uso = [
                'clientes'   => ['atual' => Cliente::count(),   'limite' => TrialLimitService::LIMITES['clientes']],
                'orcamentos' => ['atual' => Orcamento::count(), 'limite' => TrialLimitService::LIMITES['orcamentos']],
                'usuarios'   => ['atual' => User::where('perfil', '!=', 'super_admin')->count(), 'limite' => TrialLimitService::LIMITES['usuarios']],
                'pecas'      => ['atual' => Peca::count(),      'limite' => TrialLimitService::LIMITES['pecas']],
            ];
        }

        // ── Status estendido: validade, dias restantes e badge ────────────────
        $dataValidade = $tenant->trialAtivo() ? $tenant->trial_ends_at : $tenant->plano_vence_em;

        $validade      = $dataValidade ? Carbon::parse($dataValidade)->format('d/m/Y') : null;
        $diasRestantes = $dataValidade
            ? (int) now()->startOfDay()->diffInDays(Carbon::parse($dataValidade)->startOfDay(), false)
            : null;

        $statusBadge = 'success';
        if ($diasRestantes !== null) {
            if ($diasRestantes < 0) {
                $statusBadge = 'danger';
            } elseif ($diasRestantes <= 5) {
                $statusBadge = 'warning';
            }
        }

        // ── Cobranças pendentes na Asaas (apenas se houver customer_id) ────────
        $pendentes = [];
        if ($tenant->subscription && $tenant->subscription->gateway_customer_id) {
            $asaas = app(AsaasService::class);
            // null (API indisponível) tratado como vazio nesta story; aviso explícito vem na 6.4
            $pendentes = $asaas->pagamentosPendentes($tenant->subscription->gateway_customer_id) ?? [];
        }

        return view('pitstop.assinatura', compact(
            'tenant', 'logs', 'uso',
            'pendentes', 'validade', 'diasRestantes', 'statusBadge'
        ));
    }
}
