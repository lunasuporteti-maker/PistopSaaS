<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Orcamento;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        // Métricas gerais da plataforma
        $totalTenants   = Tenant::count();
        $tenantsAtivos  = Tenant::where('ativo', true)->count();
        $totalUsuarios  = User::withoutGlobalScope('tenant')->count();

        // Status de assinatura
        $emTrial  = Tenant::where('ativo', true)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->where('plano_ativo', false)
            ->count();

        $planoPago = Tenant::where('ativo', true)
            ->where('plano_ativo', true)
            ->count();

        $expirados = Tenant::where('ativo', true)
            ->where(function ($q) {
                $q->where(function ($sub) {
                    // Trial expirado e sem plano
                    $sub->whereNotNull('trial_ends_at')
                        ->where('trial_ends_at', '<=', now())
                        ->where('plano_ativo', false);
                });
            })
            ->count();

        // Tenants recentes (últimos 10)
        $recentes = Tenant::orderByDesc('created_at')->limit(10)->get();

        return view('admin.dashboard', compact(
            'totalTenants', 'tenantsAtivos', 'totalUsuarios',
            'emTrial', 'planoPago', 'expirados', 'recentes'
        ));
    }
}
