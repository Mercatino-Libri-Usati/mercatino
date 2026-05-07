<?php

namespace App\Http\Controllers;

use App\Helpers\LibroHelper;
use App\Models\Catalogo;
use App\Models\Libri;
use App\Models\Prenotazione;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Libro
 */
class UtilController extends Controller
{
    /**
     * Verifica libro accettato
     *
     * Verifica se un libro è accettato (anche con ISBN parziale) e ritorna titolo e prezzo.
     *
     * @group Libro
     *
     * @urlParam isbn string required L'ISBN completo o parziale del libro da verificare. No-example
     *
     * @responseField isbn ISBN completo del libro trovato.
     * @responseField titolo Titolo del libro trovato.
     * @responseField prezzo Prezzo di default del libro trovato.
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
     * Verifica libro prenotabile
     *
     * Verifica se un libro è prenotabile dato un ISBN, cioè se ci sono copie disponibili (non prenotate, vendute o restituite).
     *
     * @group Libro
     *
     * @urlParam isbn string required L'ISBN del libro da verificare. No-example
     *
     * @responseField message Messaggio di conferma se il libro è prenotabile.
     */
    public function isPrenotabile(string $isbn): JsonResponse
    {
        if (! LibroHelper::numeroDisponibili($isbn)) {
            return $this->notFoundResponse('Nessuna copia disponibile');
        }

        return $this->successResponse();
    }

    /**
     * Verifica libro vendibile
     *
     * Verifica se un libro è vendibile dato il numero annuo e l'ID utente, cioè se non è venduto o restituito e se la prenotazione è null o a nome dello stesso utente.
     *
     * @group Libro
     *
     * @urlParam numeroAnnuo int required Il numero annuo del libro da verificare. No-example
     *
     * @queryParam userid int required L'ID dell'utente che vuole acquistare il libro. No-example
     *
     * @responseField id ID del libro.
     * @responseField numero_annuo Il numero annuo del libro.
     * @responseField isbn L'ISBN del libro.
     * @responseField titolo Il titolo del libro.
     * @responseField prezzo Il prezzo di vendita del libro.
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

        $catalogo = Catalogo::find($libro->id_catalogo);

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
            $prenotazione = Prenotazione::find($libro->id_prenotazione);

            if ($prenotazione && $prenotazione->id_utente != $userId) {
                return $this->conflictResponse('Libro prenotato da un altro utente');
            }
        }

        return null;
    }
}
