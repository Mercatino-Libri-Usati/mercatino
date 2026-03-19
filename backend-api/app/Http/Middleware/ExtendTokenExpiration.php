<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ExtendTokenExpiration
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        if ($user && $user->currentAccessToken() instanceof PersonalAccessToken) {
            $token = $user->currentAccessToken();

            // Estende la validità aggiornando expires_at (modo corretto e semantico)
            $token->forceFill([
                'expires_at' => now()->addMinutes(60),
            ])->save();

            // Refresh cookie se presente
            if ($request->hasCookie('auth_token')) {
                $tokenValue = $request->cookie('auth_token');
                if (is_array($tokenValue)) {
                    $tokenValue = reset($tokenValue);
                }
                if ($response instanceof \Illuminate\Http\Response || $response instanceof \Illuminate\Http\JsonResponse) {
                    $response->withCookie(cookie('auth_token', $tokenValue, 60));
                }
            }
        }

        return $response;
    }
}
