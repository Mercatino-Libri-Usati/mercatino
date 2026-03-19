<?php

namespace App\Http\Controllers;

use App\Models\Operazione;
use App\Models\Restituzione;
use App\Models\Ritiro;
use App\Services\PdfRicevuteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RestituzioneController extends Controller
{
    /**
     * Restituisce i libri di una ricevuta di restituzione.
     * GET /api/ricevute/restituzione/{id}
     */
    public function showRestituzione(Request $request, int $id): JsonResponse
    {
        if ($id <= 0) {
            return $this->errorResponse('ID non valido', 422);
        }

        $metadati = $request->boolean('metadati', true);
        $libri = $request->boolean('libri', true);

        if (! $metadati && ! $libri) {
            return $this->errorResponse('Nessun parametro valido fornito', 422);
        }

        if (! Restituzione::where('ID', $id)->exists()) {
            return $this->notFoundResponse("Restituzione $id non trovata");
        }

        $risposta = [];
        if ($metadati) {
            $risposta['metadati'] = Restituzione::getMetadati($id);
        }
        if ($libri) {
            $risposta['libri'] = Restituzione::getLibri($id);
        }

        return $this->successResponse($risposta);
    }

    /**
     * Anteprima dei libri e soldi da restituire per un dato utente.
     * GET /api/ricevute/restituzione/preview?userid={userid}
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
     * Crea una nuova ricevuta di restituzione per un utente.
     * POST /api/ricevute/restituzione
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
     * Elabora la creazione della restituzione.
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

        $numeroRestituzione = $this->getNextProgressivo('restituzionen', 'numero_restituzione');

        $idRestituzione = DB::table('restituzionen')->insertGetId([
            'id_utente' => $userId,
            'data' => now(),
            'numero_restituzione' => $numeroRestituzione,
        ]);

        $libriIds = array_column($libriRestituibili, 'id');
        DB::table('libron')
            ->whereIn('id', $libriIds)
            ->update(['id_restituzione' => $idRestituzione]);

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
        DB::table('restituzionen')->where('id', $idRestituzione)->update([
            'url_pdf' => $pdfUrl,
        ]);

        return $this->createdResponse(['id_restituzione' => $idRestituzione, 'numero_restituzione' => $numeroRestituzione, 'pdf_url' => $pdfUrl]);
    }

    /**
     * Recupera i libri di un ritiro con informazioni sullo stato.
     */
    private function getLibriRitiroConStato(int|array $ritiroId): \Illuminate\Support\Collection
    {
        $query = DB::table('libron')
            ->join('catalogo', 'libron.id_libro', '=', 'catalogo.ID')
            ->select(
                'libron.id',
                'libron.numero_libro',
                'catalogo.titolo',
                'catalogo.ISBN as isbn',
                'libron.prezzo',
                'libron.id_vendita',
                'libron.id_prenotazione',
                'libron.id_restituzione'
            );

        if (is_array($ritiroId)) {
            $query->whereIn('id_ritiro', $ritiroId);
        } else {
            $query->where('id_ritiro', $ritiroId);
        }

        return $query->get();
    }
}
