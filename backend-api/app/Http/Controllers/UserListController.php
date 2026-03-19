<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UserListController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('utente')->get();

        $mapped = $users->map(function (User $user): array {
            return $this->getDatiUtente($user);
        });

        return response()->json($mapped->values());
    }

    public function show(int|string $id): JsonResponse
    {
        $user = User::with('utente')
            ->findOrFail($id);

        return response()->json($this->getDatiUtente($user));
    }

    public function indexRidotto(): JsonResponse
    {
        $users = User::with('utente')->get();

        $mapped = $users->map(function (User $user): array {
            return [
                'id' => (int) $user->ID,
                'nome_cognome' => $user->getNomeCognome(),
            ];
        });

        return response()->json($mapped->values());
    }

    public function updateUtente(int|string $id): JsonResponse
    {
        $validated = request()->validate([
            'nome' => 'nullable|string|max:50',
            'cognome' => 'nullable|string|max:50',
            'email' => 'nullable|email:rfc|max:99|unique:utenti,mail,'.$id.',ID',
            'telefono' => 'nullable|string|max:20|regex:/^[0-9\s+]*$/|min:1',
        ]);

        $user = User::with('utente')->findOrFail($id);

        if (isset($validated['email'])) {
            $validated['mail'] = $validated['email'];
            unset($validated['email']);
        }

        $dataToUpdate = array_filter($validated, fn ($value) => $value !== null);

        if (! empty($dataToUpdate)) {
            $user->utente->update($dataToUpdate);

            return response()->json(null, Response::HTTP_OK);
        }

        return response()->json(['message' => 'Fornire almeno un campo da aggiornare'], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function getDatiUtente(User $user): array
    {
        return [
            'id' => $user->ID,
            'nome' => $user->utente->nome,
            'cognome' => $user->utente->cognome,
            'nominativo' => $user->getNomeCognome(),
            'email' => $user->utente->mail,
            'telefono' => $user->utente->telefono,
            'verificato' => $this->isVerificato($user),
        ];
    }

    private function isVerificato(User $user): bool
    {
        $utenteId = $user->utente->ID;
        if (DB::table('users')->where('ID_utenti', $utenteId)->exists()) {
            return true;
        }
        if (DB::table('verificautenti')->where('ID_utenti', $utenteId)->exists()) {
            return true;
        }

        return false;
    }
}
