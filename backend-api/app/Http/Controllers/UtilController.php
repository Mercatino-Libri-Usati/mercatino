<?php

namespace App\Http\Controllers;

use App\Helpers\LibroHelper;
use App\Models\Catalogo;
use App\Models\Libri;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UtilController extends Controller
{
    /**
     * Verifica se un libro è accettato (anche con ISBN parziale) e ritorna titolo e prezzo.
     * GET /api/util/isAccettato/{isbn}
     */
    public function isAccettato(string $isbn): JsonResponse
    {
        $libri = Catalogo::where('ISBN', 'like', "%{$isbn}")->get();

        if ($libri->isEmpty()) {
            return $this->notFoundResponse(self::MSG_LIBRO_NON_TROVATO);
        }

        if ($libri->count() > 1) {
            return $this->conflictResponse(
                'Trovati più libri corrispondenti a questo ISBN parziale. Inserire più cifre.'
            );
        }

        $libro = $libri->first();

        if (! LibroHelper::isAccettato($libro->ISBN)) {
            return $this->notFoundResponse(self::MSG_LIBRO_NON_TROVATO);
        }

        return $this->successResponse([
            'isbn' => $libro->ISBN,
            'titolo' => $libro->titolo,
            'prezzo' => $libro->prezzo,
        ]);
    }

    /**
     * Verifica se un libro è prenotabile.
     * GET /api/util/isPrenotabile/{isbn}
     */
    public function isPrenotabile(string $isbn): JsonResponse
    {
        if (! LibroHelper::numeroDisponibili($isbn)) {
            return $this->notFoundResponse('Nessuna copia disponibile');
        }

        return $this->successResponse();
    }

    /**
     * Verifica se un libro è vendibile per un utente.
     * GET /api/util/isVendibile/{id}?userid={userid}
     */
    public function isVendibile(int $id, Request $request): JsonResponse
    {
        $userId = $request->query('userid');

        if (! $userId || ! is_numeric($userId)) {
            return $this->errorResponse('ID utente richiesto', Response::HTTP_BAD_REQUEST);
        }

        $libro = Libri::where('numero_libro', $id)->first();

        if (! $libro) {
            return $this->notFoundResponse(self::MSG_LIBRO_NON_TROVATO);
        }

        $validationError = $this->validateLibroVendibile($libro, (int) $userId);
        if ($validationError) {
            return $validationError;
        }

        $catalogo = Catalogo::find($libro->id_libro);

        if (! $catalogo) {
            return $this->notFoundResponse('Dati catalogo non trovati');
        }

        return $this->successResponse([
            'id' => $libro->id,
            'numero_annuo' => $libro->numero_libro,
            'isbn' => $catalogo->ISBN,
            'titolo' => $catalogo->titolo,
            'prezzo' => $libro->prezzo * config('config.percentualeVendita'),
        ]);
    }

    // Valida se un libro può essere venduto a un utente.
    private function validateLibroVendibile(Libri $libro, int $userId): ?JsonResponse
    {
        if ($libro->id_vendita !== null || $libro->id_restituzione !== null) {
            return $this->conflictResponse('Libro già venduto o restituito');
        }

        if ($libro->id_prenotazione !== null) {
            $prenotazione = DB::table('prenotazionin')
                ->where('id', $libro->id_prenotazione)
                ->first();

            if ($prenotazione && $prenotazione->id_utente != $userId) {
                return $this->conflictResponse('Libro prenotato da un altro utente');
            }
        }

        return null;
    }
}
