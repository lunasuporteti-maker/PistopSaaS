<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user           = Auth::user();
            $tokenSessao    = $request->session()->get('session_token');
            $tokenBanco     = $user->session_token;

            // Se o token da sessão atual não bate com o do banco, outro dispositivo logou
            if ($tokenBanco && $tokenSessao !== $tokenBanco) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['username' => 'Sua sessão foi encerrada porque outro acesso foi iniciado com este usuário.']);
            }
        }

        return $next($request);
    }
}
