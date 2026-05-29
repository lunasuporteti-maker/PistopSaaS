<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\Peca;
use App\Models\SubscriptionLog;
use App\Models\User;
use App\Services\TrialLimitService;

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

        return view('pitstop.assinatura', compact('tenant', 'logs', 'uso'));
    }
}
