<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureTenantSubscriptionIsCurrent;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'webhooks/paymongo',
            'api/n8n/*',
        ]);

        $middleware->alias([
            'role' => CheckRole::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'tenant.subscription' => EnsureTenantSubscriptionIsCurrent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
