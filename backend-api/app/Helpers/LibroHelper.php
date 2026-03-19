<?php

namespace App\Helpers;

use App\Models\Adozione;
use App\Models\Catalogo;
use App\Models\Libri;

class LibroHelper
{
    // Verifica se un libro è accettato (presente nel catalogo e adottato nell'anno corrente)
    public static function isAccettato(string $isbn): bool
    {
        $anno = config('config.anno');
        $libro = Catalogo::where('ISBN', $isbn)->first();

        if (! $libro) {
            return false;
        }

        return (bool) Adozione::where('ID_catalogo', $libro->ID)
            ->where('anno', $anno)
            ->exists();
    }

    // Conta quanti libri sono disponibili per un dato ISBN.
    // Un libro è disponibile se non ha prenotazione, vendita o restituzione.
    public static function numeroDisponibili(string $isbn): int
    {
        return Libri::where('ISBN', $isbn)
            ->whereNull('id_prenotazione')
            ->whereNull('id_vendita')
            ->whereNull('id_restituzione')
            ->count();
    }

    // Verifica se un libro è vendibile a un dato utente.
    // Un libro è vendibile se non ha vendite e restituzioni e se la prenotazione è null o a nome dello stesso utente.
    public static function isVendibile(int $id, int $userId): bool
    {
        $libro = Libri::find($id);

        if (! $libro) {
            return false;
        }

        if ($libro->id_vendita !== null || $libro->id_restituzione !== null) {
            return false;
        }

        if ($libro->id_prenotazione === null) {
            return true;
        }

        return $libro->id_utente === $userId;
    }
}
