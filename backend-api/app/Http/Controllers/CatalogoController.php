<?php

namespace App\Http\Controllers;

use App\Models\Adozione;
use App\Models\Catalogo;
use App\Models\Libri;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * @group Libro
 */
class CatalogoController extends Controller
{
    /**
     * Adozioni
     *
     * Restituisce tutte le classi che adottano ogni libro del catalogo.
     *
     * @group Catalogo
     *
     * @responseField id_catalogo ID del libro nel catalogo.
     * @responseField titolo Titolo del libro.
     * @responseField sottotitolo Sottotitolo del libro.
     * @responseField autore Autore del libro.
     * @responseField isbn ISBN del libro.
     * @responseField prezzo Prezzo di default del libro.
     * @responseField classi Elenco delle classi che adottano il libro, con scuola, indirizzo e classe.
     * @responseField numero_libri Numero totale di libri ritirati per questo catalogo.
     * @responseField numero_disponibili Numero di libri attualmente disponibili (non prenotati, venduti o restituiti).
     */
    public function index(): JsonResponse
    {
        $raggruppate = $this->getAdozioniRaggruppate();

        return $this->successResponse($raggruppate->toArray());
    }

    /**
     * Modifica prezzo catalogo
     *
     * Modifica il prezzo di default di un libro nel catalogo, che sarà usato per i nuovi ritiri.
     *
     * @group Catalogo
     *
     * @urlParam id_catalogo int required L'ID del libro. No-example
     *
     * @bodyParam prezzo numeric required Il nuovo prezzo. No-example
     */
    public function updatePrezzo(string $idCatalogo, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prezzo' => ['required', 'numeric', 'min:0'],
        ]);

        $catalogo = Catalogo::find($idCatalogo);

        if (! $catalogo) {
            return $this->notFoundResponse(self::MSG_LIBRO_NON_TROVATO);
        }

        $catalogo->prezzo = $validated['prezzo'];
        $catalogo->save();

        return $this->successResponse();
    }

    // Recupera e raggruppa le adozioni per catalogo, aggiungendo numero_libri e numero_disponibili.
    private function getAdozioniRaggruppate(): Collection
    {
        $adozioni = Adozione::where('anno', config('config.anno'))->get();

        $idCatalogos = $adozioni->pluck('ID_catalogo')->unique()->filter()->values();

        $cataloghi = Catalogo::whereIn('ID', $idCatalogos)->get()->keyBy('ID');

        $libriStats = Libri::selectRaw('id_catalogo, COUNT(*) as totale, SUM(CASE WHEN id_prenotazione IS NULL AND id_vendita IS NULL AND id_restituzione IS NULL THEN 1 ELSE 0 END) as disponibili')
            ->whereIn('id_catalogo', $idCatalogos)
            ->groupBy('id_catalogo')
            ->get()
            ->keyBy('id_catalogo');

        return $adozioni->groupBy('ID_catalogo')->map(function ($items, $idCatalogo) use ($cataloghi, $libriStats) {
            $catalogo = $cataloghi->get($idCatalogo);

            $stats = $libriStats->get($idCatalogo);
            $numero_libri = $stats ? (int) $stats->totale : 0;
            $numero_disponibili = $stats ? (int) $stats->disponibili : 0;

            return [
                'id_catalogo' => $idCatalogo,
                'titolo' => $catalogo->titolo ?? '',
                'sottotitolo' => $catalogo->sottotitolo ?? '',
                'autore' => $catalogo->autore ?? '',
                'isbn' => $catalogo->ISBN ?? '',
                'prezzo' => $catalogo->prezzo ?? 0,
                'classi' => $this->getClassiUniche($items),
                'numero_libri' => $numero_libri,
                'numero_disponibili' => $numero_disponibili,
            ];
        })->values();
    }

    // Estrae le combinazioni uniche di scuola/indirizzo/classe.
    private function getClassiUniche(Collection $items): array
    {
        return $items->map(fn ($item) => [
            'scuola' => $item->scuola,
            'indirizzo' => $item->indirizzo,
            'classe' => $item->classe,
        ])
            ->unique(fn ($item) => "{$item['scuola']}|{$item['indirizzo']}|{$item['classe']}")
            ->values()
            ->sort()
            ->all();
    }
}
