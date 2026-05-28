<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::define('admin', fn (User $user) => $user->perfil === 'admin');
        Gate::define('gerente_ou_admin', fn (User $user) => in_array($user->perfil, ['admin', 'gerente']));
        Gate::define('operacional', fn (User $user) => in_array($user->perfil, ['admin', 'gerente', 'operador']));
        Gate::define('acima_de_mecanico', fn (User $user) => in_array($user->perfil, ['admin', 'gerente']));

        // PRD 03, AC6 — máx 5 submissões de cadastro por hora por IP.
        RateLimiter::for('signup', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
    }
}
