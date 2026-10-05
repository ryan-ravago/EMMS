<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->throttleApi();
        $middleware->alias(['abilities' => CheckAbilities::class]);
        // $middleware->web(prepend: [
        //     'throttle:filament',
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always answer JSON on the RFID API, even if the caller forgets the Accept header.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('v1/*') || $request->expectsJson()
        );
    })->create();
