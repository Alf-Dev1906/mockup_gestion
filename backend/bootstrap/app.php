<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        api:      __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS en primer lugar, luego security headers
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserRole::class,
        ]);

        $middleware->throttleApi();

        // NO usar statefulApi() cuando se usan Bearer tokens
        // Solo activar si el frontend usa cookies de sesión de Laravel
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Respuestas JSON para errores en rutas API
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
        });
    })->create();
