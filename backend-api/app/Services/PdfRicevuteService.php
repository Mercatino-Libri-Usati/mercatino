<?php

namespace App\Services;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $filename = $tipo.'_'.$numero_ritiro.'.pdf';
        $path = 'ricevute/'.$filename;

        $pdf = Pdf::loadView('pdf.ritiro', [
            'libri' => $libri,
            'numero' => $numero_ritiro,
            'data' => $data,
            'nome_utente' => User::find($id_utente)->getNomeCognome()." (ID: $id_utente)",
            'totale' => $totale,
            'tipo' => $tipo,
        ]);
        $pdf->setPaper('A4', 'landscape');
        Storage::disk('public')->put($path, $pdf->output(), 'public');

        return route('ricevute.download', ['id' => $numero_ritiro, 'tipo' => $tipo]);
    }
}
