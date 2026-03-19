<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendita extends Model
{
    protected $table = 'venditan';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $guarded = [];

    public static function show(): \Illuminate\Database\Eloquent\Builder
    {
        return self::select(
            'ID as id',
            'data as data',
            'numero_vendita as numero',
            \Illuminate\Support\Facades\DB::raw("'Vendita' as tipo"),
            'id_utente',
            'url_pdf'
        );
    }

    /**
     * Restituisce i libri di una ricevuta di vendita.
     */
    public static function getLibri(int $idVendita): \Illuminate\Support\Collection
    {
        $libri = Libri::where('id_vendita', $idVendita)
            ->join('catalogo', 'libron.id_libro', '=', 'catalogo.ID')
            ->select(
                'libron.id',
                'libron.numero_libro',
                'catalogo.titolo',
                'catalogo.ISBN as isbn',
                'libron.prezzo as prezzo_originale',
                'libron.id_vendita',
            )
            ->get();

        $percentuale = (float) config('config.percentualeVendita');

        return $libri->map(function ($libro) use ($percentuale) {
            $libro->prezzo = round($libro->prezzo_originale * $percentuale, 2);

            return $libro;
        });
    }

    public static function esiste(int $idVendita): bool
    {
        return self::getLibri($idVendita)->isNotEmpty();
    }

    /**
     * Restituisce i dettagli di una vendita.
     */
    public static function getMetadati(int $id): ?array
    {
        $vendita = self::show()->where('id', $id)->first();

        if (! $vendita) {
            return null;
        }

        $user = User::where('ID_utenti', $vendita->id_utente)->first();

        return [
            'utente_id' => $vendita->id_utente,
            'utente_nominativo' => $user ? $user->getNomeCognome() : 'N/A',
            'numero_vendita' => $vendita->numero,
            'pdf_url' => $vendita->url_pdf,
        ];
    }
}
