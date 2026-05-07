<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @group Ricevute
 */
class RicevuteDownloadController extends Controller
{
    /**
     * PDF ricevuta
     *
     * Restituisce il PDF della ricevuta.
     *
     * @group Ricevute
     *
     * @bodyParam id int required L'ID della ricevuta da scaricare. No-example
     * @bodyParam tipo string required Il tipo di ricevuta (vendita, ritiro, restituzione). No-example
     *
     * @response file Il file PDF della ricevuta.
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
        $filename = "{$tipo}_{$id}.pdf";
        $path = 'ricevute/'.$filename;

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'Ricevuta non trovata');
        }

        return response()->file(Storage::disk('public')->path($path));
    }
}
