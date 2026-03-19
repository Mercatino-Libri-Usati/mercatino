<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RicevuteDownloadController extends Controller
{
    /**
     * Scarica o visualizza il PDF della ricevuta.
     * GET /api/ricevute/download?id=123&tipo=ritiro
     */
    public function download(Request $request): BinaryFileResponse
    {
        $request->validate([
            'id' => 'required|integer',
            'tipo' => 'required|string',
        ]);

        $validated = $request->only(['id', 'tipo']);

        $id = $validated['id'];
        $tipo = $validated['tipo'];
        // TODO: non calcolare il filename a mano, salvalo nel database quando generi la ricevuta
        $filename = "{$tipo}_{$id}.pdf";
        $path = 'ricevute/'.$filename;

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'Ricevuta non trovata');
        }

        return response()->file(Storage::disk('public')->path($path));
    }
}
