<?php

namespace App\Http\Controllers;

use App\Helpers\LibroHelper;
use App\Models\Catalogo;
use App\Models\Operazione;
use App\Models\Ritiro;
use App\Services\PdfRicevuteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RitiroController extends Controller
{
    /**
     * Restituisce i libri di una ricevuta di ritiro.
     * GET /api/ricevute/ritiro/{id}
     */
    public function showRitiro(Request $request, int $id): JsonResponse
    {
        if ($id <= 0) {
            return $this->errorResponse('ID non valido', 422);
        }

        $metadati = $request->boolean('metadati', true);
        $libri = $request->boolean('libri', true);

        if (! $metadati && ! $libri) {
            return $this->errorResponse('Nessun parametro valido fornito', 422);
        }

        if (! Ritiro::where('ID', $id)->exists()) {
            return $this->notFoundResponse("Ritiro $id non trovato");
        }

        $risposta = [];
        if ($metadati) {
            $risposta['metadati'] = Ritiro::getMetadati($id);
        }
        if ($libri) {
            $risposta['libri'] = Ritiro::getLibri($id);
        }

        return $this->successResponse($risposta);
    }

    /**
     * Inserisce una ricevuta di ritiro con più libri.
     * POST /api/ricevute/ritiro
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
        DB::beginTransaction();

        try {
            $anno = (int) now()->format('Y');
            $numeroRitiro = $this->getNextProgressivo('ritiron', 'numero_ritiro', $anno);

            $ritiroId = DB::table('ritiron')->insertGetId([
                'data' => now(),
                'id_utente' => $userId,
                'numero_ritiro' => $numeroRitiro,
            ]);

            $this->inserisciLibri($ritiroId, $elencoLibri, $anno);

            DB::commit();

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
            DB::table('ritiron')->where('id', $ritiroId)->update([
                'url_pdf' => $pdfUrl,
            ]);

            return $this->createdResponse(['id_ritiro' => $ritiroId, 'numero_ritiro' => $numeroRitiro, 'pdf_url' => $pdfUrl]);
        } catch (\Throwable $e) {
            DB::rollBack();

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

            $libroId = DB::table('libron')->insertGetId([
                'prezzo' => (float) $libro['prezzo'],
                'id_libro' => $catalogo->ID,
                'id_ritiro' => $ritiroId,
                'numero_libro' => $numeroLibro,
                'id_prenotazione' => null,
                'id_vendita' => null,
                'id_restituzione' => null,
                'note' => $libro['note'] ?? null,
            ]);
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
        $maxLibro = DB::table('libron')
            ->join('ritiron', 'libron.id_ritiro', '=', 'ritiron.id')
            ->whereYear('ritiron.data', $anno)
            ->max('libron.numero_libro');

        return $maxLibro ? $maxLibro + 1 : 1;
    }
}
