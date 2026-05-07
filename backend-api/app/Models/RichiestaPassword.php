<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RichiestaPassword extends Model
{
    protected $table = 'richieste_password';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_utente',
        'data',
        'token',
    ];

    protected $guarded = [];
}
