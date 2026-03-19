<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utente extends Model
{
    protected $table = 'utenti';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [
        'nome',
        'cognome',
        'telefono',
        'mail',
        'scuola',
        'ID_registro',
        'data',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class, 'ID_utenti', 'ID');
    }
}
