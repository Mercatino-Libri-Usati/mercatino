<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Libri extends Model
{
    protected $table = 'libron';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * Calcola il numero totale di copie fisiche presenti per un dato libro del catalogo
     */
    public static function totali(int|string $idCatalogo): int
    {
        return self::where('id_libro', $idCatalogo)->count();
    }

    /**
     * Calcola il numero di copie fisiche disponibili per un dato libro del catalogo
     */
    public static function disponibili(int|string $idCatalogo): int
    {
        return self::where('id_libro', $idCatalogo)->disponibili()->count();
    }

    /**
     * Calcola lo stato del singolo libro in base agli ID di tracciamento
     */
    public static function stato(?int $idRestituzione, ?int $idVendita, ?int $idPrenotazione): string
    {
        if ($idRestituzione) {
            return 'restituito';
        }
        if ($idVendita) {
            return 'venduto';
        }
        if ($idPrenotazione) {
            return 'prenotato';
        }

        return 'disponibile';
    }
}
