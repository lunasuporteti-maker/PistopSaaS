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
        Gate::define('admin',            fn(User $user) => $user->perfil === 'admin');
        Gate::define('gerente_ou_admin', fn(User $user) => in_array($user->perfil, ['admin', 'gerente']));
        Gate::define('operacional',       fn(User $user) => in_array($user->perfil, ['admin', 'gerente', 'operador']));
        Gate::define('acima_de_mecanico', fn(User $user) => in_array($user->perfil, ['admin', 'gerente']));
    }
}
