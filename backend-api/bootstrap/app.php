<?php

use App\Http\Middleware\ExtendTokenExpiration;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'etag' => \App\Http\Middleware\EtagCaching::class,
        ]);
        $middleware->api(prepend: [
            \App\Http\Middleware\CompressResponse::class,
            \App\Http\Middleware\AttachSanctumTokenFromCookie::class,
        ]);
        $middleware->api(append: [
            ExtendTokenExpiration::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
