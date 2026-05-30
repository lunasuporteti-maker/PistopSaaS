<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $metricas = Cache::remember('admin.metricas', 1800, function () {
            $planoPago = Tenant::where('ativo', true)->where('plano_ativo', true)->count();
            $emTrial   = Tenant::where('ativo', true)
                ->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now())
                ->where('plano_ativo', false)->count();
            $expirados = Tenant::where('ativo', true)
                ->whereNotNull('trial_ends_at')->where('trial_ends_at', '<=', now())
                ->where('plano_ativo', false)->count();

            // Novos cadastros nos últimos 7 dias
            $novosSemana = Tenant::where('created_at', '>=', now()->subDays(7))->count();

            // Top 10 tenants por OS nos últimos 30 dias
            $osIds = OrdemServico::withoutGlobalScope('tenant')
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('tenant_id, count(*) as os_30d')
                ->groupBy('tenant_id')
                ->orderByDesc('os_30d')
                ->limit(10)
                ->pluck('os_30d', 'tenant_id');

            $maisAtivos = Tenant::whereIn('id', $osIds->keys())
                ->get()
                ->map(fn ($t) => ['tenant' => $t, 'os_30d' => $osIds[$t->id] ?? 0])
                ->sortByDesc('os_30d')
                ->values();

            return [
                'totalTenants'  => Tenant::count(),
                'tenantsAtivos' => Tenant::where('ativo', true)->count(),
                'totalUsuarios' => User::withoutGlobalScope('tenant')->count(),
                'planoPago'     => $planoPago,
                'emTrial'       => $emTrial,
                'expirados'     => $expirados,
                'novosSemana'   => $novosSemana,
                'maisAtivos'    => $maisAtivos,
            ];
        });

        $recentes = Tenant::orderByDesc('created_at')->limit(10)->get();

        return view('admin.dashboard', array_merge($metricas, compact('recentes')));
    }
}
