<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adozione extends Model
{
    protected $table = 'adozioni';

    protected $primaryKey = 'ID';

    public $timestamps = false;
}
