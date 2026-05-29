<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Confia no Traefik/Coolify como proxy reverso
        $middleware->trustProxies(at: '*');

        // Webhooks externos não enviam CSRF token — excluir da verificação
        $middleware->validateCsrfTokens(except: [
            '/webhooks/*',
        ]);

        $middleware->alias([
            'tenant'              => \App\Http\Middleware\IdentifyTenant::class,
            'single.session'      => \App\Http\Middleware\EnsureSingleSession::class,
            'restrict.mecanico'   => \App\Http\Middleware\RestrictMecanico::class,
            'super.admin'         => \App\Http\Middleware\RequireSuperAdmin::class,
            'check.subscription'  => \App\Http\Middleware\CheckSubscription::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
