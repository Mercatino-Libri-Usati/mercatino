<?php

namespace App\Http\Controllers;

use App\Helpers\LibroHelper;
use App\Models\Libri;
use App\Models\Operazione;
use App\Models\Vendita;
use App\Services\PdfRicevuteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group Vendita
 */
class VenditaController extends Controller
{
    /**
     * Dettaglio vendita
     *
     * Restituisce i dati della ricevuta di vendita con i libri associati.
     *
     * @urlParam id int required L'ID della prenotazione da visualizzare. No-example
     *
     * @queryParam metadati boolean Dati della ricevuta da includere. No-example
     * @queryParam libri boolean Libri della ricevuta da includere. No-example
     *
     * @responseField id ID della ricevuta.
     * @responseField numero_vendita Numero progressivo della vendita.
     * @responseField libri Elenco dei libri della vendita.
     */
    public function showVendita(Request $request, int $id): JsonResponse
    {

        $metadati = $request->boolean('metadati', true);
        $libri = $request->boolean('libri', true);

        if (! $metadati && ! $libri) {
            return $this->errorResponse('Nessun parametro valido fornito', 422);
        }

        $risposta = Vendita::dettaglioRicevuta($id, $metadati, $libri);
        if (is_null($risposta)) {
            return $this->notFoundResponse("Vendita $id non trovata");
        }

        return $this->successResponse($risposta);
    }

    /**
     * Nuova vendita
     *
     * Crea una nuova ricevuta di vendita con uno o più libri.
     *
     * @bodyParam userid integer required L'ID dell'utente che effettua la vendita. No-example
     * @bodyParam libri array required Elenco degli ID dei libri da vendere. No-example
     *
     * @responseField id_vendita ID della ricevuta creata.
     * @responseField numero_vendita Numero progressivo della ricevuta.
     * @responseField pdf_url URL del PDF generato.
     */
    public function addVendita(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'userid' => ['required', 'integer'],
            'libri' => ['required', 'array', 'min:1', 'max:20'],
            'libri.*' => ['required', 'integer'],
        ]);

        $userId = $validated['userid'];

        if (! $this->userExists($userId)) {
            return $this->notFoundResponse(self::MSG_UTENTE_NON_TROVATO);
        }

        return $this->processVendita($userId, $validated['libri']);
    }

    /**
     * Elabora la vendita in transazione.
     */
    private function processVendita(int $userId, array $elencoLibri): JsonResponse
    {
        try {
            $vendita = DB::transaction(function () use ($userId, $elencoLibri): array {
                $numeroVendita = $this->getNextProgressivo('vendite', 'numero_vendita');

                $idVendita = Vendita::create([
                    'id_utente' => $userId,
                    'numero_vendita' => $numeroVendita,
                    'data' => now(),
                ])->id;

                foreach ($elencoLibri as $libroId) {
                    $this->assegnaLibroAVendita($libroId, $idVendita, $userId);
                }

                return [
                    'id_vendita' => $idVendita,
                    'numero_vendita' => $numeroVendita,
                ];
            });

            $idVendita = $vendita['id_vendita'];
            $numeroVendita = $vendita['numero_vendita'];

            $libriCompleti = Vendita::getLibri($idVendita);

            // Genera e salva il PDF sul server
            $pdfUrl = (new PdfRicevuteService)->generateAndSave(
                $libriCompleti,
                $numeroVendita,
                now()->format('d/m/Y H:i'),
                $userId,
                (float) $libriCompleti->sum('prezzo'),
                'vendita'
            );

            // Aggiungi il link al PDF nel db
            Vendita::aggiornaUrlPdf($idVendita, $pdfUrl);

            return $this->createdResponse(['id_vendita' => $idVendita, 'numero_vendita' => $numeroVendita, 'pdf_url' => $pdfUrl]);
        } catch (\Exception $e) {
            if ($e->getCode() === Response::HTTP_NOT_FOUND) {
                return $this->notFoundResponse($e->getMessage());
            }

            return $this->conflictResponse($e->getMessage());
        }
    }

    /**
     * Assegna libro alla vendita.
     */
    private function assegnaLibroAVendita(int $libroId, int $idVendita, int $userId): void
    {
        $libro = Libri::query()
            ->whereKey($libroId)
            ->lockForUpdate()
            ->first();

        if (! $libro) {
            throw new \Exception("Libro con ID {$libroId} non trovato", Response::HTTP_NOT_FOUND);
        }

        if (! LibroHelper::isVendibile($libroId, $userId)) {
            throw new \Exception(
                "Il libro numero {$libro->numero_libro} è già stato veduto, restrituito o prenotato da un altro utente",
            );
        }

        $libro->update(['id_vendita' => $idVendita]);

        Operazione::aggiungi([
            'tipo' => 'vendita',
            'libro' => $libro->id,
            'importo' => $libro->prezzo * config('config.percentualeVendita'),
        ]);
    }
}
