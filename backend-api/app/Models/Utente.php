<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Utente extends Model
{
    protected $table = 'utenti';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nome',
        'cognome',
        'telefono',
        'mail',
        'scuola',
        'id_registro',
        'data',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(Credenziali::class, 'id_utente', 'id');
    }

    public function getNomeCognome(): string
    {
        return "{$this->nome} {$this->cognome}";
    }
}
