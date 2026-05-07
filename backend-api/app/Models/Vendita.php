<?php

namespace App\Models;

use App\Models\Concerns\GestisceRicevute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Vendita extends Model
{
    use GestisceRicevute;

    protected $table = 'vendite';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];

    public static function show(): Builder
    {
        return self::select(
            'id',
            'data as data',
            'numero_vendita as numero',
            DB::raw("'Vendita' as tipo"),
            'id_utente',
            'url_pdf'
        );
    }

    /**
     * Restituisce i libri di una ricevuta di vendita.
     */
    public static function getLibri(int $idVendita): Collection
    {
        $libri = Libri::where('id_vendita', $idVendita)
            ->join('catalogo', 'libri.id_catalogo', '=', 'catalogo.ID')
            ->select(
                'libri.id',
                'libri.numero_libro',
                'catalogo.titolo',
                'catalogo.ISBN as isbn',
                'libri.prezzo as prezzo_originale',
                'libri.id_vendita',
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

        $user = Credenziali::where('id_utente', $vendita->id_utente)->first();

        return [
            'utente_id' => $vendita->id_utente,
            'utente_nominativo' => $user?->getNomeCognome() ?? 'N/A',
            'numero_vendita' => $vendita->numero,
            'pdf_url' => $vendita->url_pdf,
        ];
    }
}
