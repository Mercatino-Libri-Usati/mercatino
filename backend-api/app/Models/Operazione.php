<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use function Illuminate\Support\now;

class Operazione extends Model
{
    public $timestamps = false;

    protected $table = 'log_operazioni';

    protected $fillable = [
        'data',
        'tipo',
        'libro',
        'operatore',
        'importo',
        'causale',
    ];

    protected $casts = [
        'data' => 'date',
        'importo' => 'decimal:2',
    ];

    /**
     * Elencare tutte le operazioni con filtri opzionali
     */
    public static function elenco(): Collection
    {
        $query = self::query();

        return $query->orderBy('data')->get();
    }

    /**
     * Aggiungere una nuova operazione
     */
    public static function aggiungi(array $dati): self
    {
        return self::create([
            'data' => now(),
            'tipo' => $dati['tipo'],
            'libro' => $dati['libro'] ?? null,
            'operatore' => Auth::id(),
            'importo' => round((float) $dati['importo'], 2),
            'causale' => $dati['causale'] ?? null,
        ]);
    }

    /**
     * Rimuovere un'operazione
     */
    public static function togli(int|string $id): int
    {
        return self::destroy($id);
    }
}
