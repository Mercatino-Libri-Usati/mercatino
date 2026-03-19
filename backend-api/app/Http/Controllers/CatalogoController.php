<?php

namespace App\Http\Controllers;

use App\Models\Adozione;
use App\Models\Catalogo;
use App\Models\Libri;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    /**
     * Restituisce tutte le adozioni raggruppate per libro.
     * GET /api/adozioni
     */
    public function index(): JsonResponse
    {
        $raggruppate = $this->getAdozioniRaggruppate();

        return $this->successResponse($raggruppate->toArray());
    }

    /**
     * Modifica il prezzo di un libro dato l'id catalogo.
     * PUT /api/catalogo/{id}/prezzo
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
    private function getAdozioniRaggruppate(): \Illuminate\Support\Collection
    {
        $adozioni = Adozione::where('anno', config('config.anno'))->get();

        $idCatalogos = $adozioni->pluck('ID_catalogo')->unique()->filter()->values();

        $cataloghi = Catalogo::whereIn('ID', $idCatalogos)->get()->keyBy('ID');

        $libriStats = Libri::selectRaw('id_libro, COUNT(*) as totale, SUM(CASE WHEN id_prenotazione IS NULL AND id_vendita IS NULL AND id_restituzione IS NULL THEN 1 ELSE 0 END) as disponibili')
            ->whereIn('id_libro', $idCatalogos)
            ->groupBy('id_libro')
            ->get()
            ->keyBy('id_libro');

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
    private function getClassiUniche(\Illuminate\Support\Collection $items): array
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
