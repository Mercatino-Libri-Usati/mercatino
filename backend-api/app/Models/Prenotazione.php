<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prenotazione extends Model
{
    protected $table = 'prenotazionen';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $guarded = [];
}
