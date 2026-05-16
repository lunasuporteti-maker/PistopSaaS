<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictMecanico
{
    // Rotas que o mecânico PODE acessar (além de auth/perfil/logout)
    private array $rotasPermitidas = [
        'dashboard',
        'kanban',
        'kanban.status',
        'fila',
        'agendamentos',
        'agendamentos.index',
        'agendamentos.show',
        'agendamentos.create',
        'agendamentos.store',
        'agendamentos.edit',
        'agendamentos.update',
        'agendamentos.destroy',
        'perfil.edit',
        'perfil.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->perfil === 'mecanico') {
            $rotaAtual = $request->route()?->getName();

            if ($rotaAtual && ! in_array($rotaAtual, $this->rotasPermitidas)) {
                return redirect()->route('fila')
                    ->with('error', 'Acesso restrito. Seu perfil permite apenas a Fila de Serviços e Agendamentos.');
            }
        }

        return $next($request);
    }
}
