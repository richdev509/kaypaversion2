<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            '2fa' => \App\Http\Middleware\CheckDeviceAnd2FA::class,
        ]);

        // Ne PAS appliquer globalement, la vérification est faite au login
        // $middleware->append(\App\Http\Middleware\CheckDeviceAnd2FA::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Gérer l'erreur "Page Expired" (419 - Token CSRF expiré/Session expirée)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Votre session a expiré. Veuillez vous reconnecter.',
                    'redirect' => route('login')
                ], 419);
            }

            return redirect()->route('login')
                ->with('info', 'Votre session a expiré. Veuillez vous reconnecter.');
        });
    })->create();
