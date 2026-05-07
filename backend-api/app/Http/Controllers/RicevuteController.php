<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\Credenziali;
use App\Models\Libri;
use App\Models\Prenotazione;
use App\Models\Restituzione;
use App\Models\Ritiro;
use App\Models\Utente;
use App\Models\Vendita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Ricevute
 */
class RicevuteController extends Controller
{
    /**
     * Elenco ricevute
     *
     * Mostra l'elenco delle ricevute registrate nel sistema, con eventuale filtraggio.
     *
     * @queryParam numero string Numero progressivo della ricevuta. No-example
     * @queryParam utente string Nome o cognome dell'utente associato alla ricevuta. No-example
     * @queryParam libro string Numero del libro associato alla ricevuta. No-example
     * @queryParam isbn string ISBN del libro associato alla ricevuta. No-example
     * @queryParam tipo string[] Tipo di ricevuta (Vendita, Ritiro, Restituzione). No-example
     * @queryParam date_from string Data di inizio per il filtro (formato YYYY-MM-DD). No-example
     * @queryParam date_to string Data di fine per il filtro (formato YYYY-MM-DD). No-example
     * @queryParam user_id int ID dell'utente associato alla ricevuta. No-example
     *
     * @responseField id ID della ricevuta.
     * @responseField data Data di creazione della ricevuta.
     * @responseField numero Numero progressivo della ricevuta.
     * @responseField tipo Tipo di ricevuta (Vendita, Ritiro, Restituzione).
     * @responseField id_utente ID dell'utente associato alla ricevuta.
     * @responseField pdf_url URL del PDF della ricevuta.
     * @responseField nominativo Nome e cognome dell'utente associato alla ricevuta.
     */
    public function index(Request $request): JsonResponse
    {
        $numero = $request->input('numero');
        $utente = (string) $request->string('utente');
        $libro = $request->input('libro');
        $isbn = (string) $request->string('isbn');
        $tipo = $request->input('tipo');
        /** @var string|null $dateFrom */
        $dateFrom = $request->input('date_from');
        /** @var string|null $dateTo */
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id');

        // Unione delle query
        $query = Ritiro::show()->unionAll(Prenotazione::show())->unionAll(Vendita::show())
            ->unionAll(Restituzione::show());

        // Applicazione filtri
        $unionQuery = Ritiro::query()->fromSub($query, 'ricevute_unite');

        if ($numero) {
            $unionQuery->where('numero', $numero);
        }

        if ($tipo) {
            $unionQuery->whereIn('tipo', $tipo);
        }

        if ($dateFrom) {
            $unionQuery->whereDate('data', '>=', $dateFrom);
        }

        if ($dateTo) {
            $unionQuery->whereDate('data', '<=', $dateTo);
        }

        if ($userId) {
            $unionQuery->where('id_utente', $userId);
        }

        if ($utente) {
            $idUtente = Utente::query()
                ->whereRaw("CONCAT(nome, ' ', cognome) like ?", ["%{$utente}%"])
                ->pluck('id')
                ->all();

            $unionQuery->whereIn('id_utente', $idUtente);
        }

        if ($isbn) {
            $idCatalogo = Catalogo::where('ISBN', 'like', "%$isbn%")->pluck('ID')->all();

            $idRicevute = Libri::query()
                ->whereIn('id_catalogo', $idCatalogo)
                ->select('id_ritiro', 'id_prenotazione', 'id_vendita', 'id_restituzione')
                ->get()
                ->flatMap(function ($row) {
                    return array_filter([
                        $row->id_ritiro,
                        $row->id_prenotazione,
                        $row->id_vendita,
                        $row->id_restituzione,
                    ]);
                })
                ->unique()
                ->values()
                ->all();

            $unionQuery->whereIn('id', $idRicevute);
        }

        if ($libro) {
            $libro = (array) $libro;

            $idRicevute = Libri::query()
                ->whereIn('numero_libro', $libro)
                ->select('id_ritiro', 'id_prenotazione', 'id_vendita', 'id_restituzione')
                ->get()
                ->flatMap(function ($row) {
                    return array_filter([
                        $row->id_ritiro,
                        $row->id_prenotazione,
                        $row->id_vendita,
                        $row->id_restituzione,
                    ]);
                })
                ->unique()
                ->values()
                ->all();

            $unionQuery->whereIn('id', $idRicevute);
        }

        $unionQuery->orderByRaw("FIELD(tipo, 'Restituzione', 'Vendita', 'Ritiro')")
            ->orderBy('numero', 'desc');

        $ricevute = $unionQuery->get();
        // Recupera tutti gli utenti che hanno ricevute in un'unica query
        $userIds = $ricevute->pluck('id_utente')->unique()->filter()->all();
        $users = Credenziali::whereIn('id_utente', $userIds)->get()->keyBy('id_utente');
        $ricevute->transform(function ($ricevuta) use ($users) {
            $user = $users->get($ricevuta->id_utente);
            $ricevuta->nominativo = $user ? $user->getNomeCognome() : 'Sconosciuto';

            return $ricevuta;
        });

        return response()->json($ricevute);
    }
}
