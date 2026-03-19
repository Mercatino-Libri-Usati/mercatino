<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Restituzione extends Model
{
    protected $table = 'restituzionen';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $guarded = [];

    public static function show(): Builder
    {
        return self::select(
            'ID as id',
            'data as data',
            'numero_restituzione as numero',
            \Illuminate\Support\Facades\DB::raw("'Restituzione' as tipo"),
            'id_utente',
            'url_pdf'
        );
    }

    public static function getLibri(int $idRestituzione): Collection
    {
        $libri = \App\Models\Libri::where('id_restituzione', $idRestituzione)
            ->join('catalogo', 'libron.id_libro', '=', 'catalogo.ID')
            ->select(
                'libron.id',
                'libron.numero_libro',
                'catalogo.titolo',
                'catalogo.ISBN as isbn',
                'libron.prezzo as prezzo_originale',
                'libron.id_restituzione',
                'libron.id_vendita'
            )
            ->get();

        $percentuale = (float) config('config.percentualeRestituzione');

        return $libri->map(function ($libro) use ($percentuale) {
            if (is_null($libro->id_vendita)) {
                $libro->prezzo = 0.00;
            } else {
                $libro->prezzo = round($libro->prezzo_originale * $percentuale, 2);
            }

            return $libro;
        });
    }

    public static function esiste(int $idRestituzione): bool
    {
        return self::getLibri($idRestituzione)->isNotEmpty();
    }

    /**
     * Restituisce i dettagli completi di una restituzione.
     */
    public static function getMetadati(int $id): ?array
    {
        $restituzione = self::show()->where('id', $id)->first();

        if (! $restituzione) {
            return null;
        }

        $user = User::where('ID_utenti', $restituzione->id_utente)->first();

        return [
            'utente_id' => $restituzione->id_utente,
            'utente_nominativo' => $user ? $user->getNomeCognome() : 'N/A',
            'numero_restituzione' => $restituzione->numero,
            'pdf_url' => $restituzione->url_pdf,
        ];
    }
}
