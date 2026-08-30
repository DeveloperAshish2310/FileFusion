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
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            '/pwa/share-target',
            'pwa/share-target',
            'api/*',
            'cron/*',
            '/cron/*',
            'panel/vault/lock-beacon',
            '/panel/vault/lock-beacon',
        ]);
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'api.token' => \App\Http\Middleware\AuthenticateApiToken::class,
        ]);
        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'ok' => 0,
                    'code' => 413,
                    'info' => 'The uploaded payload exceeds the server maximum single POST limit (' . (ini_get('post_max_size') ?: '8M') . '). Please upload via chunked transfer.',
                ], 413);
            }

            return response()->view('errors.413', [
                'exception' => $e,
                'postMaxSize' => ini_get('post_max_size'),
                'uploadMaxFilesize' => ini_get('upload_max_filesize'),
            ], 413);
        });
    })->create();
