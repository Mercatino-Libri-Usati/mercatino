<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Libri extends Model
{
    protected $table = 'libri';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * Calcola lo stato del singolo libro in base agli ID di tracciamento
     */
    public static function stato(?int $idRestituzione, ?int $idVendita, ?int $idPrenotazione): string
    {
        return match (true) {
            (bool) $idRestituzione => 'restituito',
            (bool) $idVendita => 'venduto',
            (bool) $idPrenotazione => 'prenotato',
            default => 'disponibile',
        };
    }
}
