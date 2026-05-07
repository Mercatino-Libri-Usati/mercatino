<?php

namespace App\Http\Controllers;

use App\Helpers\LibroHelper;
use App\Models\Catalogo;
use App\Models\Libri;
use App\Models\Operazione;
use App\Models\Ritiro;
use App\Services\PdfRicevuteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group Ritiro
 */
class RitiroController extends Controller
{
    /**
     * Dettaglio ritiro
     *
     * Restituisce i dati della ricevuta di ritiro con i libri associati.
     *
     * @urlParam id int required L'ID della prenotazione da visualizzare. No-example
     *
     * @queryParam metadati boolean Dati della ricevuta da includere. No-example
     * @queryParam libri boolean Libri della ricevuta da includere. No-example
     *
     * @responseField id ID della ricevuta.
     * @responseField numero_ritiro Numero progressivo del ritiro.
     * @responseField libri Elenco dei libri del ritiro.
     */
    public function showRitiro(Request $request, int $id): JsonResponse
    {

        $metadati = $request->boolean('metadati', true);
        $libri = $request->boolean('libri', true);

        if (! $metadati && ! $libri) {
            return $this->errorResponse('Nessun parametro valido fornito', 422);
        }

        $risposta = Ritiro::dettaglioRicevuta($id, $metadati, $libri);
        if (is_null($risposta)) {
            return $this->notFoundResponse("Ritiro $id non trovato");
        }

        return $this->successResponse($risposta);
    }

    /**
     * Nuovo ritiro
     *
     * Crea una nuova ricevuta di ritiro con uno o più libri.
     *
     * @bodyParam userid integer required L'ID dell'utente che effettua il ritiro. No-example
     * @bodyParam libri array required Elenco dei libri da ritirare. No-example
     *
     * @responseField id_ritiro ID della ricevuta creata.
     * @responseField numero_ritiro Numero progressivo della ricevuta.
     * @responseField pdf_url URL del PDF generato.
     */
    public function addRitiro(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'userid' => ['required', 'integer'],
            'libri' => ['required', 'array', 'min:1', 'max:20'],
            'libri.*.isbn' => ['required', 'string', 'max:13'],
            'libri.*.prezzo' => ['required', 'numeric', 'min:0'],
            'libri.*.note' => ['sometimes', 'nullable', 'string', 'max:1024'],
        ]);

        $userId = $validated['userid'];

        if (! $this->userExists($userId)) {
            return $this->notFoundResponse(self::MSG_UTENTE_NON_TROVATO);
        }

        return $this->processRitiro($userId, $validated['libri']);
    }

    // Elabora l'inserimento del ritiro in una transazione.
    private function processRitiro(int $userId, array $elencoLibri): JsonResponse
    {
        try {
            $ritiro = DB::transaction(function () use ($userId, $elencoLibri): array {
                $anno = (int) now()->format('Y');
                $numeroRitiro = $this->getNextProgressivo('ritiri', 'numero_ritiro', $anno);

                $ritiroId = Ritiro::create([
                    'data' => now(),
                    'numero_ritiro' => $numeroRitiro,
                    'id_utente' => $userId,
                ])->id;

                $this->inserisciLibri($ritiroId, $elencoLibri, $anno);

                return [
                    'id_ritiro' => $ritiroId,
                    'numero_ritiro' => $numeroRitiro,
                ];
            });

            $ritiroId = $ritiro['id_ritiro'];
            $numeroRitiro = $ritiro['numero_ritiro'];

            // Recupera i dati completi dei libri appena inseriti
            $libriCompleti = Ritiro::getLibri($ritiroId);

            // Genera e salva il PDF sul server
            $pdfUrl = (new PdfRicevuteService)->generateAndSave(
                $libriCompleti,
                $numeroRitiro,
                now()->format('d/m/Y H:i'),
                $userId,
                (float) $libriCompleti->sum('prezzo'),
                'ritiro'
            );

            // Aggiungi il link al PDF nel db
            Ritiro::aggiornaUrlPdf($ritiroId, $pdfUrl);

            return $this->createdResponse(['id_ritiro' => $ritiroId, 'numero_ritiro' => $numeroRitiro, 'pdf_url' => $pdfUrl]);
        } catch (\Throwable $e) {
            return $this->errorResponse(self::MSG_ERRORE_INTERNO.'('.$e->getMessage().')', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // Inserisce i libri collegati al ritiro.
    private function inserisciLibri(int $ritiroId, array $elencoLibri, int $anno): void
    {
        foreach ($elencoLibri as $libro) {
            $isbn = (string) $libro['isbn'];

            if (! LibroHelper::isAccettato($isbn)) {
                throw new \Exception("Il libro con ISBN {$isbn} non è accettato per il ritiro");
            }

            $catalogo = Catalogo::where('ISBN', $isbn)->firstOrFail();
            $numeroLibro = $this->getNextNumeroLibro($anno);

            $libroId = Libri::create([
                'prezzo' => (float) $libro['prezzo'],
                'id_catalogo' => $catalogo->ID,
                'id_ritiro' => $ritiroId,
                'numero_libro' => $numeroLibro,
                'id_prenotazione' => null,
                'id_vendita' => null,
                'id_restituzione' => null,
                'note' => $libro['note'] ?? null,
            ])->id;
            Operazione::aggiungi([
                'tipo' => 'ritiro',
                'libro' => $libroId,
                'importo' => 0,
            ]);
        }
    }

    // Calcola il prossimo numero progressivo annuale per i libri.
    private function getNextNumeroLibro(int $anno): int
    {
        $maxLibro = Libri::query()
            ->join('ritiri', 'libri.id_ritiro', '=', 'ritiri.id')
            ->whereYear('ritiri.data', $anno)
            ->max('libri.numero_libro');

        return $maxLibro ? $maxLibro + 1 : 1;
    }
}
