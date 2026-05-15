<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Gate para acesso exclusivo admin
        Gate::define('admin', fn(User $user) => $user->perfil === 'admin');

        // Gate para gerente ou admin
        Gate::define('gerente_ou_admin', fn(User $user) => in_array($user->perfil, ['admin', 'gerente']));

        // Gate para qualquer usuário autenticado com acesso operacional
        Gate::define('operacional', fn(User $user) => in_array($user->perfil, ['admin', 'gerente', 'operador']));
    }
}
