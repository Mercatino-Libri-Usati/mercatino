<?php

namespace App\Services;

use App\Mail\RicevutaCreata;
use App\Models\Utente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PdfRicevuteService
{
    /**
     * Genera e salva il PDF di una ricevuta sul server.
     *
     * @param  mixed  $libri  Elenco dei libri
     * @return string URL del PDF salvato
     */
    public function generateAndSave($libri, int $numero_ritiro, string $data, int $id_utente, float $totale, string $tipo): string
    {
        $utente = Utente::query()->find($id_utente);

        $path = 'ricevute/'.$tipo.'_'.$numero_ritiro.'.pdf';

        $pdf = Pdf::loadView('pdf.ritiro', [
            'libri' => $libri,
            'numero' => $numero_ritiro,
            'data' => $data,
            'nome_utente' => $utente ? $utente->getNomeCognome()." (ID: $id_utente)" : "Utente sconosciuto (ID: $id_utente)",
            'totale' => $totale,
            'tipo' => $tipo,
        ]);
        $pdf->setPaper('A4', 'landscape');
        Storage::disk('public')->put($path, $pdf->output(), 'public');

        // Invia la mail all'utente
        if ($utente?->mail && env('APP_ENV') === 'production') {
            Mail::to($utente->mail)->send(new RicevutaCreata($path));
        }

        return route('ricevute.download', ['id' => $numero_ritiro, 'tipo' => $tipo]);
    }
}
