<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictMecanico
{
    // Rotas que o operador (mecânico) PODE acessar
    private array $rotasPermitidas = [
        'kanban',
        'kanban.estado',
        'kanban.status',
        'kanban.arquivar',
        'fila',
        'agendamentos',
        'agendamentos.index',
        'agendamentos.show',
        'agendamentos.create',
        'agendamentos.store',
        'agendamentos.edit',
        'agendamentos.update',
        'agendamentos.destroy',
        'agendamentos.concluir',
        'agendamentos.iniciar-servico',
        'perfil.edit',
        'perfil.update',
        'perfil.update.dados',
        'json.veiculos-por-cliente',
        'json.clientes.store',
        'json.veiculos.store',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->perfil === 'operador') {
            $rotaAtual = $request->route()?->getName();

            if ($rotaAtual && ! in_array($rotaAtual, $this->rotasPermitidas)) {
                return redirect()->route('kanban')
                    ->with('error', 'Acesso restrito. Seu perfil permite apenas o Kanban, Fila de Serviços e Agendamentos.');
            }
        }

        return $next($request);
    }
}
