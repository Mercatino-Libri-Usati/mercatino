<?php

namespace App\Http\Controllers;

use App\Models\Libri;
use Illuminate\Http\JsonResponse;

/**
 * @group Libro
 */
class LibriController extends Controller
{
    /**
     * Elenco libri
     *
     * Restituisce l'elenco completo dei libri presenti nel sistema con il loro stato (es. venduto, reso).
     *
     * @responseField id ID del libro.
     * @responseField titolo Titolo del libro.
     * @responseField stato Stato attuale del libro nel sistema.
     */
    public function index(): JsonResponse
    {
        $libri = Libri::query()
            ->join('catalogo', 'libri.id_catalogo', '=', 'catalogo.ID')
            ->join('ritiri', 'libri.id_ritiro', '=', 'ritiri.id')
            ->join('utenti', 'ritiri.id_utente', '=', 'utenti.id')
            ->select(
                'libri.id',
                'libri.numero_libro as numero',
                'catalogo.ISBN as isbn',
                'catalogo.titolo',
                'libri.prezzo',
                'libri.id_ritiro',
                'libri.id_prenotazione',
                'libri.id_vendita',
                'libri.id_restituzione',
                'libri.note',
            )
            ->selectRaw("CONCAT(utenti.nome, ' ', utenti.cognome) as proprietario")
            ->orderBy('libri.numero_libro', 'desc')
            ->get();

        foreach ($libri as $libro) {
            $libro->stato = Libri::stato($libro->id_restituzione, $libro->id_vendita, $libro->id_prenotazione);
        }

        return response()->json($libri);
    }

    /**
     * Modifica note libro
     *
     * Aggiorna le note associate a uno specifico libro.
     *
     * @urlParam id int required L'ID del libro. No-example
     *
     * @bodyParam note string Le nuove note da inserire. No-example
     *
     * @responseField message Messaggio di conferma.
     */
    public function modificaNote(int $id): JsonResponse
    {

        $validated = request()->validate([
            'note' => 'nullable|string|max:255',
        ]);

        Libri::where('id', $id)->update(['note' => $validated['note']]);

        return response()->json(['message' => 'Note aggiornate con successo']);
    }

    /**
     * Modifica prezzo libro
     *
     * Aggiorna eccezionalmente il prezzo di uno specifico libro scavalcando il catalogo.
     *
     * @urlParam id int required L'ID del libro. No-example
     *
     * @bodyParam prezzo numeric required Il nuovo prezzo. No-example
     *
     * @responseField message Messaggio di conferma.
     */
    public function modificaPrezzo(int $id): JsonResponse
    {
        $validated = request()->validate([
            'prezzo' => 'required|numeric|min:0',
        ]);

        Libri::where('id', $id)->update(['prezzo' => round($validated['prezzo'], 2)]);

        return response()->json(['message' => 'Prezzo aggiornato con successo']);
    }
}
