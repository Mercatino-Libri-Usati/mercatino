<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\Libri;
use App\Models\Prenotazione;
use App\Services\PdfRicevuteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group Prenotazioni
 */
class PrenotazioneController extends Controller
{
    /**
     * Dettaglio prenotazione
     *
     * Restituisce i dati della ricevuta di prenotazione con i libri associati.
     *
     * @urlParam id int required L'ID della prenotazione da visualizzare. No-example
     *
     * @queryParam metadati boolean Dati della ricevuta da includere. No-example
     * @queryParam libri boolean Libri della ricevuta da includere. No-example
     *
     * @responseField id ID della ricevuta.
     * @responseField numero_prenotazione Numero progressivo della prenotazione.
     * @responseField libri Elenco dei libri della prenotazione.
     */
    public function showPrenotazione(Request $request, int $id): JsonResponse
    {

        $metadati = $request->boolean('metadati', true);
        $libri = $request->boolean('libri', true);

        if (! $metadati && ! $libri) {
            return $this->errorResponse('Nessun parametro valido fornito', 422);
        }

        $risposta = Prenotazione::dettaglioRicevuta($id, $metadati, $libri);
        if (is_null($risposta)) {
            return $this->notFoundResponse("Prenotazione $id non trovata");
        }

        return $this->successResponse($risposta);
    }

    /**
     * Elenco prenotazioni
     *
     * Restituisce tutte le prenotazioni registrate.
     *
     * @responseField id_libro ID del libro prenotato.
     * @responseField numero_libro Numero progressivo del libro.
     * @responseField isbn ISBN del libro.
     * @responseField titolo Titolo del libro.
     */
    public function indexPrenotazioni(): JsonResponse
    {
        $dati = DB::table('prenotazioni')
            ->join('libri', 'prenotazioni.id', '=', 'libri.id_prenotazione')
            ->join('catalogo', 'libri.id_catalogo', '=', 'catalogo.ID')
            ->join('utenti', 'prenotazioni.id_utente', '=', 'utenti.id')
            ->leftJoin('vendite', 'libri.id_vendita', '=', 'vendite.id')
            ->select(
                'libri.id as id_libro',
                'libri.numero_libro',
                'catalogo.ISBN as isbn',
                'catalogo.titolo',
                'prenotazioni.id as id_prenotazione',
                'prenotazioni.numero_prenotazione',
                'utenti.nome as utente_nome',
                'utenti.cognome as utente_cognome',
                'vendite.numero_vendita as vendita',
                'vendite.id as id_vendita'
            )
            ->get();

        return $this->successResponse($dati->toArray());
    }

    /**
     * Nuova prenotazione
     *
     * Crea una nuova ricevuta di prenotazione con uno o più libri.
     *
     * @bodyParam userid integer required L'ID dell'utente che effettua la prenotazione. No-example
     * @bodyParam isbn array required Elenco degli ISBN da prenotare. No-example
     *
     * @responseField id_prenotazione ID della ricevuta creata.
     * @responseField numero_prenotazione Numero progressivo della ricevuta.
     * @responseField pdf_url URL del PDF generato.
     */
    public function addPrenotazione(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'userid' => ['required', 'integer'],
            'isbn' => ['required', 'array', 'min:1', 'max:20'],
            'isbn.*' => ['required', 'string', 'max:13'],
        ]);

        $userId = $validated['userid'];

        if (! $this->userExists($userId)) {
            return $this->notFoundResponse(self::MSG_UTENTE_NON_TROVATO);
        }

        return $this->processPrenotazione($userId, $validated['isbn']);
    }

    /**
     * Elabora la prenotazione in transazione.
     */
    private function processPrenotazione(int $userId, array $elencoIsbn): JsonResponse
    {
        try {
            $prenotazione = DB::transaction(function () use ($userId, $elencoIsbn): array {
                $numeroPrenotazione = $this->getNextProgressivo('prenotazioni', 'numero_prenotazione');

                $idPrenotazione = Prenotazione::create([
                    'id_utente' => $userId,
                    'numero_prenotazione' => $numeroPrenotazione,
                ])->id;

                foreach ($elencoIsbn as $isbn) {
                    $this->assegnaLibroAPrenotazione($isbn, $idPrenotazione);
                }

                return [
                    'id_prenotazione' => $idPrenotazione,
                    'numero_prenotazione' => $numeroPrenotazione,
                ];
            });

            $idPrenotazione = $prenotazione['id_prenotazione'];
            $numeroPrenotazione = $prenotazione['numero_prenotazione'];

            $libriCompleti = Prenotazione::getLibri($idPrenotazione);

            // Genera e salva il PDF sul server
            $pdfUrl = (new PdfRicevuteService)->generateAndSave(
                $libriCompleti,
                $numeroPrenotazione,
                now()->format('d/m/Y H:i'),
                $userId,
                (float) $libriCompleti->sum('prezzo'),
                'prenotazione'
            );

            // Aggiungi il link al PDF nel db
            Prenotazione::aggiornaUrlPdf($idPrenotazione, $pdfUrl);

            return $this->createdResponse(['id_prenotazione' => $idPrenotazione, 'numero_prenotazione' => $numeroPrenotazione, 'pdf_url' => $pdfUrl]);
        } catch (\Exception $e) {
            if ($e->getCode() === Response::HTTP_NOT_FOUND) {
                return $this->notFoundResponse($e->getMessage());
            }

            return $this->conflictResponse($e->getMessage());
        }
    }

    /**
     * Libri scambiabili
     *
     * Restituisce i libri disponibili per sostituire un libro prenotato.
     *
     * @urlParam isbn string required L'ISBN del libro di riferimento. No-example
     *
     * @responseField id ID del libro disponibile.
     * @responseField numero_libro Numero progressivo del libro.
     * @responseField note Note del libro.
     */
    public function libriScambiabili(string $isbn): JsonResponse
    {
        $idCatalogo = Catalogo::where('ISBN', $isbn)->value('ID');

        if (! $idCatalogo) {
            return $this->notFoundResponse('Libro non trovato nel catalogo');
        }

        $libri = Libri::query()
            ->where('id_catalogo', $idCatalogo)
            ->whereNull('id_prenotazione')
            ->whereNull('id_vendita')
            ->whereNull('id_restituzione')
            ->join('ritiri', 'libri.id_ritiro', '=', 'ritiri.id')
            ->join('utenti', 'ritiri.id_utente', '=', 'utenti.id')
            ->select(
                'libri.id',
                'libri.numero_libro',
                'libri.note',
                'utenti.nome as utente_nome',
                'utenti.cognome as utente_cognome'
            )
            ->get();

        return $this->successResponse($libri->toArray());
    }

    /**
     * Scambia libro prenotazione
     *
     * Sostituisce un libro già assegnato a una prenotazione con un altro libro disponibile.
     *
     * @bodyParam vecchio_id integer required ID del libro da sostituire. No-example
     * @bodyParam nuovo_id integer required ID del nuovo libro. No-example
     *
     * @responseField message Messaggio di esito dell'operazione.
     */
    public function scambiaLibroPrenotazione(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vecchio_id' => ['required', 'integer', 'exists:libri,id'],
            'nuovo_id' => ['required', 'integer', 'exists:libri,id'],
        ]);

        $vecchioLibro = Libri::find($validated['vecchio_id']);
        $nuovoLibro = Libri::find($validated['nuovo_id']);

        if ($vecchioLibro->id_prenotazione === null) {
            return $this->conflictResponse('Il vecchio libro selezionato non è attualmente assegnato a nessuna prenotazione');
        }
        if ($nuovoLibro->id_prenotazione !== null || $nuovoLibro->id_vendita !== null || $nuovoLibro->id_restituzione !== null) {
            return $this->conflictResponse('Il nuovo libro selezionato non è assegnabile a questa prenotazione');
        }

        $nuovoLibro->update([
            'id_prenotazione' => $vecchioLibro->id_prenotazione,
        ]);

        $vecchioLibro->update([
            'id_prenotazione' => null,
        ]);

        return $this->successResponse();

    }

    /**
     * Rimuovi libro prenotazione
     *
     * Rimuove l'assegnazione di un libro da una prenotazione.
     *
     * @bodyParam id_libro integer required ID del libro da scollegare. No-example
     *
     * @responseField message Messaggio di esito dell'operazione.
     */
    public function rimuoviLibroPrenotazione(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_libro' => ['required', 'integer', 'exists:libri,id'],
        ]);

        $libro = Libri::find($validated['id_libro']);
        if ($libro->id_prenotazione === null) {
            return $this->conflictResponse('Il libro selezionato non è attualmente assegnato a nessuna prenotazione');
        }
        $libro->update([
            'id_prenotazione' => null,
        ]);

        return $this->successResponse();
    }

    /*
     * Verifica e assegna un libro alla prenotazione.
     */
    private function assegnaLibroAPrenotazione(string $isbn, ?int $idPrenotazione): void
    {
        $idCatalogo = Catalogo::where('ISBN', $isbn)->value('ID');

        $libro = Libri::query()
            ->where('id_catalogo', $idCatalogo)
            ->whereNull('id_prenotazione')
            ->whereNull('id_vendita')
            ->whereNull('id_restituzione')
            ->inRandomOrder()
            ->lockForUpdate()
            ->first();

        if (! $libro) {
            throw new \Exception("Nessun libro disponibile con ISBN {$isbn}", Response::HTTP_NOT_FOUND);
        }

        $libro->update(['id_prenotazione' => $idPrenotazione]);
    }
}
