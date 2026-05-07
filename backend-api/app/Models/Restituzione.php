<?php

namespace App\Models;

use App\Models\Concerns\GestisceRicevute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Restituzione extends Model
{
    use GestisceRicevute;

    protected $table = 'restituzioni';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];

    public static function show(): Builder
    {
        return self::select(
            'id',
            'data as data',
            'numero_restituzione as numero',
            DB::raw("'Restituzione' as tipo"),
            'id_utente',
            'url_pdf'
        );
    }

    public static function getLibri(int $idRestituzione): Collection
    {
        $libri = Libri::where('id_restituzione', $idRestituzione)
            ->join('catalogo', 'libri.id_catalogo', '=', 'catalogo.ID')
            ->select(
                'libri.id',
                'libri.numero_libro',
                'catalogo.titolo',
                'catalogo.ISBN as isbn',
                'libri.prezzo as prezzo_originale',
                'libri.id_restituzione',
                'libri.id_vendita'
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

        $user = Credenziali::where('id_utente', $restituzione->id_utente)->first();

        return [
            'utente_id' => $restituzione->id_utente,
            'utente_nominativo' => $user?->getNomeCognome() ?? 'N/A',
            'numero_restituzione' => $restituzione->numero,
            'pdf_url' => $restituzione->url_pdf,
        ];
    }
}
