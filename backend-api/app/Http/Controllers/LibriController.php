<?php

namespace App\Http\Controllers;

use App\Models\Libri;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LibriController extends Controller
{
    public function index(): JsonResponse
    {
        $libri = DB::table('libron')
            ->join('catalogo', 'libron.id_libro', '=', 'catalogo.ID')
            ->join('ritiron', 'libron.id_ritiro', '=', 'ritiron.id')
            ->join('utenti', 'ritiron.id_utente', '=', 'utenti.ID')
            ->select(
                'libron.id',
                'libron.numero_libro as numero',
                'catalogo.ISBN as isbn',
                'catalogo.titolo',
                'libron.prezzo',
                'libron.id_ritiro',
                'libron.id_prenotazione',
                'libron.id_vendita',
                'libron.id_restituzione',
                'libron.note',
                DB::raw("CONCAT(utenti.nome, ' ', utenti.cognome) as proprietario"),
            )
            ->orderBy('libron.numero_libro', 'desc')
            ->get();

        foreach ($libri as $libro) {
            $libro->stato = Libri::stato($libro->id_restituzione, $libro->id_vendita, $libro->id_prenotazione);
        }

        return response()->json($libri);
    }

    public function modificaNote(int $id): JsonResponse
    {

        $validated = request()->validate([
            'note' => 'nullable|string|max:255',
        ]);

        Libri::where('id', $id)->update(['note' => $validated['note']]);

        return response()->json(['message' => 'Note aggiornate con successo']);
    }

    public function modificaPrezzo(int $id): JsonResponse
    {
        $validated = request()->validate([
            'prezzo' => 'required|numeric|min:0',
        ]);

        Libri::where('id', $id)->update(['prezzo' => round($validated['prezzo'], 2)]);

        return response()->json(['message' => 'Prezzo aggiornato con successo']);
    }
}
