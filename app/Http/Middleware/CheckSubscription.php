<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Não autenticado: deixa o sistema de auth lidar
        if (! $user) {
            return $next($request);
        }

        // Super admin não está vinculado a tenant — sempre pode acessar
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Obtém o tenant atual (já resolvido pelo IdentifyTenant)
        if (! app()->bound('tenant')) {
            return $next($request);
        }

        $tenant = app('tenant');

        // Tenants sem trial_ends_at são "legados" (criados antes do sistema de planos)
        // → acesso liberado para não quebrar quem já está usando
        if ($tenant->trial_ends_at === null && ! $tenant->plano_ativo) {
            return $next($request);
        }

        // Plano pago ativo e dentro do prazo → OK
        if ($tenant->emDia()) {
            return $next($request);
        }

        // Trial ativo → OK
        if ($tenant->trialAtivo()) {
            return $next($request);
        }

        // Tudo expirado → redireciona para a página de assinatura
        // Permite requisições AJAX/JSON retornarem 402 em vez de redirect
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Sua assinatura expirou.'], 402);
        }

        return redirect()->route('assine')->with('aviso', 'Seu acesso expirou. Escolha um plano para continuar.');
    }
}
