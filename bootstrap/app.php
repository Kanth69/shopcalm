<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'guest.admin' => \App\Http\Middleware\RedirectIfAdmin::class,
            'profile.setup' => \App\Http\Middleware\CheckProfileSetup::class,
        ]);

        $middleware->append(\App\Http\Middleware\CheckBlockedStatus::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Global exception handling for API routes
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {

                // Common API error formats
                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Resource not found.'
                    ], 404);
                }

                if ($e instanceof AccessDeniedHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized action.'
                    ], 403);
                }

                // Default API error
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Internal Server Error.',
                    'errors' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace()
                    ] : []
                ], method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
            }
        });
    })->create();
