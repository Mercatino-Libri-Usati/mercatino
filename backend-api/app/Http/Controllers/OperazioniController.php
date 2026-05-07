<?php

namespace App\Http\Controllers;

use App\Models\Operazione;
use Illuminate\Http\JsonResponse;

/**
 * @group Cassa
 */
class OperazioniController extends Controller
{
    /**
     * Elimina operazione
     *
     * Elimina una operazione manuale dal registro di cassa.
     *
     * @urlParam id int required L'ID dell'operazione da eliminare. No-example
     *
     * @responseField message Messaggio di esito dell'operazione.
     */
    public function deleteOperazione(int|string $id): JsonResponse
    {
        $operazione = Operazione::find($id);
        if (! $operazione) {
            return response()->json(['message' => 'Operazione non trovata'], 404);
        }
        if ($operazione->tipo !== 'manuale') {
            return response()->json(['message' => 'Solo le operazioni manuali possono essere eliminate'], 403);
        }

        $operazione->delete();

        return response()->json(['message' => 'Operazione eliminata con successo']);
    }

    /**
     * Elenco operazioni
     *
     * Restituisce tutte le operazioni di cassa ordinate dalla più recente.
     */
    public function listAll(): JsonResponse
    {
        $operazioni = Operazione::query()
            ->orderBy('data', 'desc')
            ->get();

        return response()->json($operazioni);
    }

    /**
     * Stato cassa
     *
     * Restituisce il bilancio totale della cassa e il riepilogo giornaliero.
     *
     * @responseField bilancio Bilancio totale della cassa.
     * @responseField bilancio_giornaliero Bilancio raggruppato per giorno.
     */
    public function calcolaCassa(): JsonResponse
    {
        $bilancioGiornaliero = Operazione::selectRaw('DATE(data) as giorno, SUM(importo) as bilancio')
            ->groupBy('giorno')
            ->orderBy('giorno', 'desc')
            ->get();

        $bilancioTotale = Operazione::sum('importo');

        return response()->json([
            'bilancio' => (float) $bilancioTotale,
            'bilancio_giornaliero' => $bilancioGiornaliero,
        ]);
    }

    /**
     * Nuova operazione
     *
     * Inserisce manualmente una nuova operazione di cassa.
     *
     * @bodyParam importo numeric required Importo dell'operazione. No-example
     * @bodyParam causale string La causale dell'operazione. No-example
     *
     * @responseField id ID dell'operazione creata.
     * @responseField importo Importo dell'operazione.
     * @responseField tipo Tipo dell'operazione.
     * @responseField causale Causale dell'operazione.
     */
    public function aggiungiOperazioneManuale(): JsonResponse
    {
        $validated = request()->validate([
            'importo' => 'required|numeric',
            'causale' => 'nullable|string|max:255',
        ]);

        $validated['tipo'] = 'manuale';

        $operazione = Operazione::aggiungi($validated);

        return response()->json($operazione);
    }
}
