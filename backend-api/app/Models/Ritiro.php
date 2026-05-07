<?php

namespace App\Models;

use App\Models\Concerns\GestisceRicevute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Ritiro extends Model
{
    use GestisceRicevute;

    protected $table = 'ritiri';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];

    public static function show(): Builder
    {
        return self::select(
            'id',
            'data as data',
            'numero_ritiro as numero',
            DB::raw("'Ritiro' as tipo"),
            'id_utente',
            'url_pdf'
        );
    }

    public static function getLibri(int $idRitiro): Collection
    {
        $libri = Libri::where('id_ritiro', $idRitiro)
            ->join('catalogo', 'libri.id_catalogo', '=', 'catalogo.ID')
            ->select(
                'libri.id',
                'libri.numero_libro',
                'catalogo.titolo',
                'catalogo.ISBN as isbn',
                'libri.prezzo as prezzo_originale',
                'libri.id_ritiro',
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
     * Restituisce i dettagli completi di un ritiro.
     */
    public static function getMetadati(int $id): ?array
    {
        $ritiro = self::show()->where('id', $id)->first();

        if (! $ritiro) {
            return null;
        }

        $user = Credenziali::where('id_utente', $ritiro->id_utente)->first();

        return [
            'utente_id' => $ritiro->id_utente,
            'utente_nominativo' => $user?->getNomeCognome() ?? 'N/A',
            'numero_ritiro' => $ritiro->numero,
            'pdf_url' => $ritiro->url_pdf,
        ];
    }
}
