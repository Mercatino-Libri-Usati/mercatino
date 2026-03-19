<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (is_array($acceptEncoding)) {
            $acceptEncoding = implode(', ', $acceptEncoding);
        }
        if (stripos((string) $acceptEncoding, 'gzip') === false) {
            return $response;
        }

        $content = $response->getContent();
        // Comprimi solo se più di 1 KB
        if (strlen($content) < 1024) {
            return $response;
        }

        $compressed = gzencode($content, 6);
        if ($compressed !== false) {
            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('Content-Length', strlen($compressed));
            $response->headers->set('Vary', 'Accept-Encoding');
        }

        return $response;
    }
}
