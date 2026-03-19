<?php

namespace App\Http\Controllers;

use App\Models\Restituzione;
use App\Models\Ritiro;
use App\Models\User;
use App\Models\Vendita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RicevuteController extends Controller
{
    /**
     * Display a listing of receipts.
     * GET /api/ricevute
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
        $query = Ritiro::show()->unionAll(Vendita::show())
            ->unionAll(Restituzione::show());

        // Applicazione filtri
        $unionQuery = DB::query()->fromSub($query, 'ricevute_unite');

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
            $idUtente = DB::table('utenti')
                ->where(DB::raw("CONCAT(nome, ' ', cognome)"), 'like', "%$utente%")
                ->pluck('ID')
                ->all();

            $unionQuery->whereIn('id_utente', $idUtente);
        }

        if ($isbn) {
            $idCatalogo = DB::table('catalogo')
                ->where('ISBN', 'like', "%$isbn%")
                ->pluck('ID');

            $idRicevute = DB::table('libron')
                ->whereIn('id_libro', $idCatalogo)
                ->selectRaw('id_ritiro, id_prenotazione, id_vendita, id_restituzione')
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

            $idRicevute = DB::table('libron')
                ->whereIn('numero_libro', $libro)
                ->selectRaw('id_ritiro, id_prenotazione, id_vendita, id_restituzione')
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
        $users = User::whereIn('ID_utenti', $userIds)->get()->keyBy('ID_utenti');
        $ricevute->transform(function ($ricevuta) use ($users) {
            $user = $users->get($ricevuta->id_utente);
            $ricevuta->nominativo = $user ? $user->getNomeCognome() : 'Sconosciuto';

            return $ricevuta;
        });

        return response()->json($ricevute);
    }
}
