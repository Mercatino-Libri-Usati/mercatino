<?php

namespace App\Http\Controllers;

use App\Models\Libri;
use App\Models\Operazione;
use App\Models\Utente;
use Illuminate\Http\JsonResponse;

/**
 * @group Cassa
 */
class StatsController extends Controller
{
    /**
     * Statistiche
     *
     * Ottiene statistiche generali sul sistema, come numero di libri e incassi.
     *
     * @responseField n_libri Numero totale di libri.
     * @responseField venduti Numero di libri venduti.
     * @responseField n_utenti Numero di utenti.
     * @responseField profitto Profitto totale in cassa.
     * @responseField ricavo Ricavo totale dalle vendite.
     */
    public function getStats(): JsonResponse
    {
        return $this->successResponse([
            'n_libri' => Libri::count(),
            'venduti' => Libri::whereNotNull('id_vendita')->count(),
            'n_utenti' => Utente::count(),
            'profitto' => Operazione::sum('importo'),
            'ricavo' => Operazione::where('tipo', 'vendita')->sum('importo'),
        ]);
    }
}
