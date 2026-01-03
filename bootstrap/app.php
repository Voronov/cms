<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'approved' => \App\Http\Middleware\EnsureApproved::class,
            'nocache' => \App\Http\Middleware\DisableCache::class,
        ]);
        $middleware->web(prepend: [
            \App\Http\Middleware\LocaleMiddleware::class,
            \App\Http\Middleware\DetectSite::class,
        ]);
        $middleware->web(append: [
            // \App\Http\Middleware\MinifyHtml::class,
        ]);
        $middleware->trimStrings(except: [
            'password',
            'password_confirmation',
            'blocks',
            'system_config',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });
    })->create();
