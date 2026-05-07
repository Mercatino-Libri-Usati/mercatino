<?php

namespace App\Models;

use App\Models\Concerns\GestisceRicevute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Prenotazione extends Model
{
    use GestisceRicevute;

    protected $table = 'prenotazioni';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];

    public static function show(): Builder
    {
        return self::select(
            'id',
            'data as data',
            'numero_prenotazione as numero',
            DB::raw("'Prenotazione' as tipo"),
            'id_utente',
            'url_pdf'
        );
    }

    public static function getLibri(int $idPrenotazione): Collection
    {
        $libri = Libri::where('id_prenotazione', $idPrenotazione)
            ->join('catalogo', 'libri.id_catalogo', '=', 'catalogo.ID')
            ->select(
                'libri.id',
                'libri.numero_libro',
                'catalogo.titolo',
                'catalogo.ISBN as isbn',
                'libri.prezzo as prezzo',
                'libri.id_prenotazione',
            )
            ->get();

        return $libri;
    }
}
