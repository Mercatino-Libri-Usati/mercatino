<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Credenziali extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'credenziali';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_utente',
        'nickname',
        'password',
        'privilegi',
        'attivo',
        'id_registro',
        'sede',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    public function utente(): BelongsTo
    {
        return $this->belongsTo(Utente::class, 'id_utente', 'id');
    }

    public function getNomeCognome(): string
    {
        return $this->utente ? "{$this->utente->nome} {$this->utente->cognome}" : $this->nickname;
    }
}
