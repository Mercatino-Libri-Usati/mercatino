<?php

namespace App\Http\Controllers;

use App\Helpers\LibroHelper;
use App\Models\Operazione;
use App\Models\Vendita;
use App\Services\PdfRicevuteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VenditaController extends Controller
{
    /**
     * Restituisce i libri di una ricevuta di vendita.
     * metadati=true per i dettagli della vendita (utente, data, ecc), libri=true per i libri associati
     * GET /api/ricevute/vendita/{id}?metadati=true&libri=true
     */
    public function showVendita(Request $request, int $id): JsonResponse
    {
        if ($id <= 0) {
            return $this->errorResponse('ID non valido', 422);
        }

        $metadati = $request->boolean('metadati', true);
        $libri = $request->boolean('libri', true);

        if (! $metadati && ! $libri) {
            return $this->errorResponse('Nessun parametro valido fornito', 422);
        }

        if (! Vendita::where('ID', $id)->exists()) {
            return $this->notFoundResponse("Vendita $id non trovata");
        }

        $risposta = [];
        if ($metadati) {
            $risposta['metadati'] = Vendita::getMetadati($id);
        }
        if ($libri) {
            $risposta['libri'] = Vendita::getLibri($id);
        }

        return $this->successResponse($risposta);
    }

    /**
     * Inserisce una ricevuta di vendita con più libri.
     * POST /api/ricevute/vendita
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
     * Elabora l'inserimento della vendita in una transazione.
     */
    private function processVendita(int $userId, array $elencoLibri): JsonResponse
    {
        DB::beginTransaction();

        try {
            $numeroVendita = $this->getNextProgressivo('venditan', 'numero_vendita');

            $idVendita = DB::table('venditan')->insertGetId([
                'id_utente' => $userId,
                'data' => now(),
                'numero_vendita' => $numeroVendita,
            ]);

            foreach ($elencoLibri as $libroId) {
                $this->assegnaLibroAVendita($libroId, $idVendita, $userId);
            }

            DB::commit();

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
            DB::table('venditan')->where('id', $idVendita)->update([
                'url_pdf' => $pdfUrl,
            ]);

            return $this->createdResponse(['id_vendita' => $idVendita, 'numero_vendita' => $numeroVendita, 'pdf_url' => $pdfUrl]);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($e->getCode() === Response::HTTP_NOT_FOUND) {
                return $this->notFoundResponse($e->getMessage());
            }

            return $this->conflictResponse($e->getMessage());
        }
    }

    /**
     * Verifica e assegna un libro alla vendita.
     */
    private function assegnaLibroAVendita(int $libroId, int $idVendita, int $userId): void
    {
        $libro = DB::table('libron')
            ->where('id', $libroId)
            ->lockForUpdate()
            ->first();

        if (! $libro) {
            throw new \Exception("Libro con ID {$libroId} non trovato", Response::HTTP_NOT_FOUND);
        }

        if (! LibroHelper::isVendibile($libroId, $userId)) {
            throw new \Exception(
                "Libro con numero {$libroId} non vendibile a questo utente (già venduto/restituito o prenotato da altro utente)"
            );
        }

        DB::table('libron')
            ->where('id', $libro->id)
            ->update(['id_vendita' => $idVendita]);

        Operazione::aggiungi([
            'tipo' => 'vendita',
            'libro' => $libro->id,
            'importo' => $libro->prezzo * config('config.percentualeVendita'),
        ]);
    }
}
