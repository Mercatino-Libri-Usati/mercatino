<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EtagCaching
{
    // Il client inviera l'md5 dell'ultima richiesta a questo endpoint
    // se è guale la richiesta da invaiare a quella richiesta già fatta
    // inviare solo ok

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Genera ETag dal contenuto
        $content = $response->getContent();
        // Non applicare ETag se il contenuto è troppo piccolo
        if (strlen($content) < 1024) {
            return $response;
        }

        $etag = '"'.md5($content).'"';

        // Ottieni il Etag
        $clientEtag = $request->header('If-None-Match');

        // Se corrisponde non rinviare il contenuto ma solo 304
        if ($clientEtag === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'no-cache, must-revalidate')
                ->header('Vary', 'Accept-Encoding');
        }

        // Imposta header di cache
        $response->headers->set('ETag', $etag);
        $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
        $response->headers->set('Vary', 'Accept-Encoding');

        return $response;
    }
}
