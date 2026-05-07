<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrivilege
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, int $minLevel): Response
    {
        if (! $request->user() || $request->user()->privilegi < $minLevel) {
            return response()->json(['message' => 'Non autorizzato: servono privilegi di livello '.$minLevel], 403);
        }

        return $next($request);
    }
}
