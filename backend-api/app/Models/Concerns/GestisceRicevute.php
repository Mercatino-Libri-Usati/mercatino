<?php

namespace App\Models\Concerns;

trait GestisceRicevute
{
    /**
     * Costruisce la risposta dettaglio della ricevuta (metadati/libri).
     */
    public static function dettaglioRicevuta(int $id, bool $metadati = true, bool $libri = true): ?array
    {
        $model = new static;
        $chiave = $model->getKeyName();

        if (! static::where($chiave, $id)->exists()) {
            return null;
        }

        $risposta = [];
        if ($metadati) {
            $risposta['metadati'] = static::getMetadati($id);
        }
        if ($libri) {
            $risposta['libri'] = static::getLibri($id);
        }

        return $risposta;
    }

    /**
     * Aggiorna il PDF associato alla ricevuta.
     */
    public static function aggiornaUrlPdf(int $id, string $pdfUrl): void
    {
        $model = new static;
        $chiave = $model->getKeyName();

        static::where($chiave, $id)->update([
            'url_pdf' => $pdfUrl,
        ]);
    }
}
