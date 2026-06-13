<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Requisições de API (Bearer token): o guard sanctum ainda não rodou
        // neste ponto da pilha. Resolvemos o usuário do token para pegar o
        // tenant_id correto, em vez de cair no fallback (que vazaria entre tenants).
        if (! $user && $request->bearerToken()) {
            $user = auth('sanctum')->user();
        }

        // Super admin não pertence a tenant — acesso irrestrito ao /admin
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = null;

        // Resolve pelo tenant_id do usuário autenticado (domínio único app.iaqueatende.com.br)
        if ($user && $user->tenant_id) {
            $tenant = Tenant::where('id', $user->tenant_id)->where('ativo', true)->first();
        }

        // Fallback para desenvolvimento local (DEFAULT_TENANT_SLUG no .env)
        if (! $tenant) {
            $defaultSlug = config('app.default_tenant_slug');
            if ($defaultSlug) {
                $tenant = Tenant::where('slug', $defaultSlug)->where('ativo', true)->first();
            }
        }

        if (! $tenant) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tenant não identificado.'], 404);
            }

            abort(404, 'Oficina não encontrada ou inativa.');
        }

        app()->instance('tenant', $tenant);
        app()->instance(Tenant::class, $tenant);
        $request->merge(['_tenant' => $tenant]);

        return $next($request);
    }
}
