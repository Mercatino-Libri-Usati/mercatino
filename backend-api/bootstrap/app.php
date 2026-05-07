<?php

use App\Http\Middleware\AttachSanctumTokenFromCookie;
use App\Http\Middleware\CompressResponse;
use App\Http\Middleware\EnsurePrivilege;
use App\Http\Middleware\EtagCaching;
use App\Http\Middleware\ExtendTokenExpiration;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'etag' => EtagCaching::class,
            'privilege' => EnsurePrivilege::class,
        ]);
        $middleware->api(prepend: [
            HandleCors::class,
            CompressResponse::class,
            AttachSanctumTokenFromCookie::class,
        ]);
        $middleware->api(append: [
            ExtendTokenExpiration::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
