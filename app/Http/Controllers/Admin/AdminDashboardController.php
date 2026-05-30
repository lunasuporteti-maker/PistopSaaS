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
        // Cache apenas de valores primitivos (sem objetos Eloquent)
        $kpis = Cache::remember('admin.kpis', 1800, fn () => [
            'totalTenants'  => Tenant::count(),
            'tenantsAtivos' => Tenant::where('ativo', true)->count(),
            'totalUsuarios' => User::withoutGlobalScope('tenant')->count(),
            'planoPago'     => Tenant::where('ativo', true)->where('plano_ativo', true)->count(),
            'emTrial'       => Tenant::where('ativo', true)
                ->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now())
                ->where('plano_ativo', false)->count(),
            'expirados'     => Tenant::where('ativo', true)
                ->whereNotNull('trial_ends_at')->where('trial_ends_at', '<=', now())
                ->where('plano_ativo', false)->count(),
            'novosSemana'   => Tenant::where('created_at', '>=', now()->subDays(7))->count(),
            // Top 10: só IDs e contagens — sem objetos Eloquent no cache
            'topIds'        => OrdemServico::withoutGlobalScope('tenant')
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('tenant_id, count(*) as os_30d')
                ->groupBy('tenant_id')
                ->orderByDesc('os_30d')
                ->limit(10)
                ->pluck('os_30d', 'tenant_id')
                ->all(),
        ]);

        // Busca os modelos fora do cache
        $maisAtivos = collect();
        if (! empty($kpis['topIds'])) {
            $maisAtivos = Tenant::whereIn('id', array_keys($kpis['topIds']))
                ->get()
                ->map(fn ($t) => ['tenant' => $t, 'os_30d' => $kpis['topIds'][$t->id] ?? 0])
                ->sortByDesc('os_30d')
                ->values();
        }

        $recentes = Tenant::orderByDesc('created_at')->limit(10)->get();

        return view('admin.dashboard', array_merge(
            $kpis,
            compact('maisAtivos', 'recentes')
        ));
    }
}
