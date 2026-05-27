<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Veiculo;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminTenantController extends Controller
{
    public function index(Request $request): View
    {
        $query = Tenant::query();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('nome', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'trial'    => $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now())->where('plano_ativo', false),
                'pago'     => $query->where('plano_ativo', true),
                'expirado' => $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '<=', now())->where('plano_ativo', false),
                'legado'   => $query->whereNull('trial_ends_at')->where('plano_ativo', false),
                default    => null,
            };
        }

        $tenants = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.tenants.index', compact('tenants'));
    }

    public function show(Tenant $tenant): View
    {
        // Estatísticas do tenant (queries sem o global scope de tenant)
        $totalUsuarios  = User::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->count();
        $totalClientes  = Cliente::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->count();
        $totalVeiculos  = Veiculo::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->count();
        $totalOrcamentos = Orcamento::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->count();
        $totalOS        = OrdemServico::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->count();

        $usuarios = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        return view('admin.tenants.show', compact(
            'tenant', 'totalUsuarios', 'totalClientes', 'totalVeiculos',
            'totalOrcamentos', 'totalOS', 'usuarios'
        ));
    }

    /** Estende o trial do tenant por N dias */
    public function extenderTrial(Request $request, Tenant $tenant): RedirectResponse
    {
        $dias = max(1, min(365, (int) $request->input('dias', 30)));

        $base = ($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture())
            ? $tenant->trial_ends_at
            : now();

        $tenant->update(['trial_ends_at' => $base->addDays($dias)]);

        return back()->with('success', "Trial estendido por {$dias} dias para {$tenant->nome}.");
    }

    /** Ativa ou desativa o plano pago manualmente */
    public function togglePlano(Request $request, Tenant $tenant): RedirectResponse
    {
        $ativar = $request->boolean('ativar');
        $data   = ['plano_ativo' => $ativar];

        if ($ativar && $request->filled('vence_em')) {
            $data['plano_vence_em'] = $request->date('vence_em');
        } elseif (! $ativar) {
            $data['plano_vence_em'] = null;
        }

        $tenant->update($data);

        $msg = $ativar
            ? "Plano ativado para {$tenant->nome}."
            : "Plano desativado para {$tenant->nome}.";

        return back()->with('success', $msg);
    }

    /** Ativa ou desativa o tenant inteiro */
    public function toggleAtivo(Tenant $tenant): RedirectResponse
    {
        $tenant->update(['ativo' => ! $tenant->ativo]);
        $status = $tenant->ativo ? 'ativado' : 'desativado';
        return back()->with('success', "Tenant {$tenant->nome} {$status}.");
    }
}
