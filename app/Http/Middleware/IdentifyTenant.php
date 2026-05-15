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
        $host = $request->getHost();

        // Em desenvolvimento local, usa o slug definido no .env
        $defaultSlug = config('app.default_tenant_slug');

        $tenant = null;

        // Tenta resolver pelo host (subdomínio ou domínio customizado)
        if ($host !== 'localhost' && $host !== '127.0.0.1') {
            $tenant = Tenant::resolverPorHost($host);
        }

        // Fallback para desenvolvimento local
        if (! $tenant && $defaultSlug) {
            $tenant = Tenant::where('slug', $defaultSlug)->where('ativo', true)->first();
        }

        if (! $tenant) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tenant não identificado.',
                ], 404);
            }

            abort(404, 'Oficina não encontrada ou inativa.');
        }

        // Disponibiliza o tenant globalmente na aplicação
        app()->instance('tenant', $tenant);
        app()->instance(Tenant::class, $tenant);

        // Também disponibiliza na request para fácil acesso
        $request->merge(['_tenant' => $tenant]);

        return $next($request);
    }
}
