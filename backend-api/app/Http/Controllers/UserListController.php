<?php

namespace App\Http\Controllers;

use App\Models\Utente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @group Utenti
 */
class UserListController extends Controller
{
    /**
     * Elenco utenti
     *
     * Restituisce l'elenco completo degli utenti con i dati anagrafici e lo stato di verifica.
     *
     * @responseField id ID dell'utente.
     * @responseField nome Nome dell'utente.
     * @responseField cognome Cognome dell'utente.
     * @responseField nominativo Nome e cognome dell'utente.
     * @responseField email Indirizzo email dell'utente.
     * @responseField telefono Numero di telefono dell'utente.
     * @responseField verificato Indica se l'utente ha completato la registrazione.
     */
    public function index(): JsonResponse
    {
        $utentes = Utente::query()
            ->withExists('user as verificato')
            ->get();

        $mapped = $utentes->map(function (Utente $utente): array {
            return $this->getDatiUtente($utente);
        });

        return response()->json($mapped->values());
    }

    /**
     * Dettaglio utente
     *
     * Restituisce i dati completi di un utente specifico.
     *
     * @urlParam id int required L'ID dell'utente da visualizzare. No-example
     *
     * @responseField id ID dell'utente.
     * @responseField nome Nome dell'utente.
     * @responseField cognome Cognome dell'utente.
     * @responseField nominativo Nome e cognome dell'utente.
     * @responseField email Indirizzo email dell'utente.
     * @responseField telefono Numero di telefono dell'utente.
     * @responseField verificato Indica se l'utente ha completato la registrazione.
     */
    public function show(int|string $id): JsonResponse
    {
        $utente = Utente::query()
            ->withExists('user as verificato')
            ->findOrFail($id);

        return response()->json($this->getDatiUtente($utente));
    }

    /**
     * Elenco ridotto utenti
     *
     * Restituisce solo l'ID e il nominativo degli utenti.
     *
     * @responseField id ID dell'utente.
     * @responseField nome_cognome Nome e cognome dell'utente.
     */
    public function indexRidotto(): JsonResponse
    {
        $utentes = Utente::query()->get();

        $mapped = $utentes->map(function (Utente $utente): array {
            return [
                'id' => (int) $utente->id,
                'nome_cognome' => $utente->getNomeCognome(),
            ];
        });

        return response()->json($mapped->values());
    }

    /**
     * Aggiorna utente
     *
     * Modifica l'anagrafica e i contatti (email, telefono) di un utente.
     *
     * @urlParam id int required L'ID dell'utente da modificare. No-example
     *
     * @bodyParam nome string Il nuovo nome dell'utente. No-example
     * @bodyParam cognome string Il nuovo cognome dell'utente. No-example
     * @bodyParam email string Il nuovo indirizzo email. No-example
     * @bodyParam telefono string Il nuovo numero di telefono. No-example
     *
     * @response 200 null
     * @response 422 {
     *  "message": "Fornire almeno un campo da aggiornare"
     * }
     */
    public function updateUtente(int|string $id): JsonResponse
    {
        $validated = request()->validate([
            'nome' => 'nullable|string|max:50',
            'cognome' => 'nullable|string|max:50',
            'email' => 'nullable|email:rfc|max:99|unique:utenti,mail,'.$id.',id',
            'telefono' => 'nullable|string|max:20|regex:/^[0-9\s+]*$/|min:1',
        ]);

        $utente = Utente::query()->findOrFail($id);

        if (isset($validated['email'])) {
            $validated['mail'] = $validated['email'];
            unset($validated['email']);
        }

        $dataToUpdate = array_filter($validated, fn ($value) => $value !== null);

        if (! empty($dataToUpdate)) {
            $utente->update($dataToUpdate);

            return response()->json(null, Response::HTTP_OK);
        }

        return response()->json(['message' => 'Fornire almeno un campo da aggiornare'], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Imposta privilegi utente
     *
     * Aumenta o diminuisce i privilegi di un utente indicandone la mail.
     *
     * @group Strumenti amministrativi
     *
     * @bodyParam email string required L'indirizzo email dell'utente. No-example
     * @bodyParam livello integer required Il nuovo livello di privilegi (1: base, 2: gestore, 3: admin). No-example
     */
    public function impostaPrivilegi(): JsonResponse
    {
        $validated = request()->validate([
            'email' => 'required|email:rfc|exists:utenti,mail',
            'livello' => 'required|integer|min:1|max:3',
        ], [
            'email.exists' => "Nessun utente trovato con l'email fornita.",
        ]);

        $utente = Utente::where('mail', $validated['email'])->firstOrFail();

        if (! $utente->user) {
            return response()->json(['message' => 'L\'utente non ha completato la registrazione (nessuna credenziale).'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $utente->user->update(['privilegi' => $validated['livello']]);

        return response()->json(['message' => 'Privilegi aggiornati con successo']);
    }

    private function getDatiUtente(Utente $utente): array
    {
        return [
            'id' => $utente->id,
            'nome' => $utente->nome,
            'cognome' => $utente->cognome,
            'nominativo' => $utente->getNomeCognome(),
            'email' => $utente->mail,
            'telefono' => $utente->telefono,
            'verificato' => (bool) ($utente->verificato ?? false),
        ];
    }
}
