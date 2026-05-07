<?php

namespace App\Http\Controllers;

use App\Models\Libri;
use App\Models\Operazione;
use App\Models\Restituzione;
use App\Models\Ritiro;
use App\Services\PdfRicevuteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @group Restituzione
 */
class RestituzioneController extends Controller
{
    /**
     * Dettaglio restituzione
     *
     * Restituisce i dati della ricevuta di restituzione con i libri associati.
     *
     * @urlParam id int required L'ID della prenotazione da visualizzare. No-example
     *
     * @queryParam metadati boolean Dati della ricevuta da includere. No-example
     * @queryParam libri boolean Libri della ricevuta da includere. No-example
     *
     * @responseField id ID della ricevuta.
     * @responseField numero_restituzione Numero progressivo della restituzione.
     * @responseField libri Elenco dei libri della restituzione.
     */
    public function showRestituzione(Request $request, int $id): JsonResponse
    {

        $metadati = $request->boolean('metadati', true);
        $libri = $request->boolean('libri', true);

        if (! $metadati && ! $libri) {
            return $this->errorResponse('Nessun parametro valido fornito', 422);
        }

        $risposta = Restituzione::dettaglioRicevuta($id, $metadati, $libri);
        if (is_null($risposta)) {
            return $this->notFoundResponse("Restituzione $id non trovata");
        }

        return $this->successResponse($risposta);
    }

    /**
     * Anteprima restituzione
     *
     * Mostra i libri restituibili e il valore stimato per un utente.
     *
     * @queryParam userid int required L'ID dell'utente da analizzare. No-example
     *
     * @responseField ricevute_ritiro Elenco sintetico delle ricevute di ritiro.
     * @responseField libri Elenco dei libri restituibili.
     * @responseField profitto_utente Importo stimato da restituire all'utente.
     */
    public function previewRestituzione(Request $request): JsonResponse
    {
        $userId = $request->query('userid');

        if (! $userId || ! is_numeric($userId)) {
            return $this->errorResponse('ID utente mancante o non valido', Response::HTTP_BAD_REQUEST);
        }
        $ricevuteRitiro = Ritiro::show()->where('id_utente', $userId)->get();
        $percentualeRestituzione = config('config.percentualeRestituzione');

        $libriOut = [];
        $profittoUtente = 0;

        $ritiroIds = $ricevuteRitiro->pluck('id')->toArray();
        $tuttiLibri = empty($ritiroIds) ? collect() : $this->getLibriRitiroConStato($ritiroIds);

        foreach ($tuttiLibri as $libro) {
            if (! is_null($libro->id_prenotazione) || ! is_null($libro->id_restituzione)) {
                continue;
            }

            $venduto = ! is_null($libro->id_vendita);
            $libriOut[] = [
                'numero_libro' => $libro->numero_libro,
                'titolo' => $libro->titolo,
                'isbn' => $libro->isbn,
                'prezzo' => $libro->prezzo * $percentualeRestituzione * ($venduto ? 1 : 0),
                'venduto' => $venduto,
            ];

            if ($venduto) {
                $profittoUtente += $libro->prezzo * $percentualeRestituzione;
            }
        }

        return $this->successResponse([
            'ricevute_ritiro' => range(0, max(0, count($ricevuteRitiro) - 1)),
            'libri' => $libriOut,
            'profitto_utente' => round($profittoUtente, 2),
        ]);
    }

    /**
     * Nuova restituzione
     *
     * Crea una nuova ricevuta di restituzione per un utente.
     *
     * @bodyParam userid integer required L'ID dell'utente che effettua la restituzione. No-example
     *
     * @responseField id_restituzione ID della ricevuta creata.
     * @responseField numero_restituzione Numero progressivo della ricevuta.
     * @responseField pdf_url URL del PDF generato.
     */
    public function addRestituzione(Request $request): JsonResponse
    {

        $validated = $request->validate([
            'userid' => ['required', 'integer'],
        ]);

        $userId = $validated['userid'];

        if (! $this->userExists($userId)) {
            return $this->notFoundResponse(self::MSG_UTENTE_NON_TROVATO);
        }

        return $this->processRestituzione($userId);
    }

    /**
     * Elabora la restituzione.
     */
    private function processRestituzione(int $userId): JsonResponse
    {
        $ricevuteRitiro = Ritiro::show()->where('id_utente', $userId)->get();
        $libriRestituibili = [];

        $ritiroIds = $ricevuteRitiro->pluck('id')->toArray();
        $tuttiLibri = empty($ritiroIds) ? collect() : $this->getLibriRitiroConStato($ritiroIds);

        foreach ($tuttiLibri as $libro) {
            // Escludi i libri già restituiti
            if (! is_null($libro->id_restituzione)) {
                continue;
            }
            // Se il libro è prenotato dai errore
            if (! is_null($libro->id_prenotazione)) {
                return $this->errorResponse(
                    "Il libro con numero {$libro->numero_libro} è prenotato. Annullare prima la prenotazione.",
                    Response::HTTP_BAD_REQUEST
                );
            }
            $libriRestituibili[] = $libro;
        }

        if (empty($libriRestituibili)) {
            return $this->errorResponse('Nessun libro da restituire', Response::HTTP_BAD_REQUEST);
        }

        $numeroRestituzione = $this->getNextProgressivo('restituzioni', 'numero_restituzione');

        $idRestituzione = Restituzione::create([
            'data' => now(),
            'numero_restituzione' => $numeroRestituzione,
            'id_utente' => $userId,
        ])->id;

        $libriIds = array_column($libriRestituibili, 'id');

        Libri::whereIn('id', $libriIds)->update(['id_restituzione' => $idRestituzione]);

        $libriCompleti = Restituzione::getLibri($idRestituzione);

        foreach ($libriCompleti as $libro) {

            Operazione::aggiungi([
                'tipo' => 'restituzione',
                'libro' => $libro->id,
                'importo' => -$libro->prezzo,
            ]);
        }

        // Genera e salva il PDF sul server
        $pdfUrl = (new PdfRicevuteService)->generateAndSave(
            $libriCompleti,
            $numeroRestituzione,
            now()->format('d/m/Y H:i'),
            $userId,
            (float) $libriCompleti->sum('prezzo'),
            'restituzione'
        );
        // Aggiungi il link al PDF nel db
        Restituzione::aggiornaUrlPdf($idRestituzione, $pdfUrl);

        return $this->createdResponse(['id_restituzione' => $idRestituzione, 'numero_restituzione' => $numeroRestituzione, 'pdf_url' => $pdfUrl]);
    }

    /**
     * Libri del ritiro con stato
     *
     * Recupera i libri di uno o più ritiri con le informazioni sullo stato.
     */
    private function getLibriRitiroConStato(int|array $ritiroId): Collection
    {
        $query = Libri::query()
            ->join('catalogo', 'libri.id_catalogo', '=', 'catalogo.ID')
            ->select(
                'libri.id',
                'libri.numero_libro',
                'catalogo.titolo',
                'catalogo.ISBN as isbn',
                'libri.prezzo',
                'libri.id_vendita',
                'libri.id_prenotazione',
                'libri.id_restituzione'
            );

        if (is_array($ritiroId)) {
            $query->whereIn('id_ritiro', $ritiroId);
        } else {
            $query->where('id_ritiro', $ritiroId);
        }

        return $query->get();
    }
}
