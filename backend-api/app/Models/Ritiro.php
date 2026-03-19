<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Ritiro extends Model
{
    protected $table = 'ritiron';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $guarded = [];

    public static function show(): Builder
    {
        return self::select(
            'ID as id',
            'data as data',
            'numero_ritiro as numero',
            \Illuminate\Support\Facades\DB::raw("'Ritiro' as tipo"),
            'id_utente',
            'url_pdf'
        );
    }

    public static function getLibri(int $idRitiro): Collection
    {
        $libri = \App\Models\Libri::where('id_ritiro', $idRitiro)
            ->join('catalogo', 'libron.id_libro', '=', 'catalogo.ID')
            ->select(
                'libron.id',
                'libron.numero_libro',
                'catalogo.titolo',
                'catalogo.ISBN as isbn',
                'libron.prezzo as prezzo_originale',
                'libron.id_ritiro',
            )
            ->get();

        $percentuale = (float) config('config.percentualeRestituzione');

        return $libri->map(function ($libro) use ($percentuale) {
            $libro->prezzo = round($libro->prezzo_originale * $percentuale, 2);

            return $libro;
        });
    }

    public static function esiste(int $idRitiro): bool
    {
        return self::getLibri($idRitiro)->isNotEmpty();
    }

    /**
     * Restituisce i dettagli completi di una ritiro.
     */
    public static function getMetadati(int $id): ?array
    {
        $ritiro = self::show()->where('id', $id)->first();

        if (! $ritiro) {
            return null;
        }

        $user = User::where('ID_utenti', $ritiro->id_utente)->first();

        return [
            'utente_id' => $ritiro->id_utente,
            'utente_nominativo' => $user ? $user->getNomeCognome() : 'N/A',
            'numero_ritiro' => $ritiro->numero,
            'pdf_url' => $ritiro->url_pdf,
        ];
    }
}
