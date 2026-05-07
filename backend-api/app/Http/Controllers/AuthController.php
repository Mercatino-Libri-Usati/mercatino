<?php

namespace App\Http\Controllers;

use App\Models\Credenziali;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @group Registrazione
 */
class AuthController extends Controller
{
    /**
     * Login utente
     *
     * Autentica un utente nel sistema e restituisce i dati della sessione.
     *
     * @bodyParam login string required La mail o il nickname dell'utente. No-example
     * @bodyParam password string required La password dell'utente. No-example
     *
     * @responseField userid L'id dell'utente.
     * @responseField privilegio Il livello di privilegio.
     * @responseField nome_cognome Nome e cognome dell'utente.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $login = $request->input('login');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'mail' : 'nickname';

        if ($field === 'mail') {
            $user = Credenziali::whereHas('utente', function ($query) use ($login) {
                $query->where('mail', $login);
            })->first();
        } else {
            $user = Credenziali::where('nickname', $login)->first();
        }

        // Use MD5 check as per existing database state
        if (! $user || md5($request->password) !== $user->password) {
            throw ValidationException::withMessages([
                'login' => ['Le credenziali fornite non sono corrette.'],
            ]);
        }
        // Pulizia automatica: rimuovi i token scaduti di questo utente prima di crearne uno nuovo
        $user->tokens()->where('expires_at', '<', now())->delete();

        // Crea il token con scadenza di 60 minuti
        $token = $user->createToken('api-token', ['*'], now()->addMinutes(60))->plainTextToken;

        $cookie = cookie('auth_token', $token, 60, null, null, null, true);

        return response()->json([
            'userid' => $user->utente->id,
            'privilegio' => $user->privilegi,
            'nome_cognome' => $user->utente->nome.' '.$user->utente->cognome,
        ])->withCookie($cookie);
    }

    /**
     * Logout utente
     *
     * Effettua il logout dell'utente revoca il token di accesso.
     *
     * @responseField message The status of this API (`up` or `down`).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete(); // Elimina tutti i token dell'utente
        }
        $cookie = cookie()->forget('auth_token');

        return response()->json(['message' => 'Logout effettuato con successo.'])->withCookie($cookie);
    }

    /**
     * Dati sessione corrente
     *
     * Restituisce le informazioni essenziali dell'utente attualmente autenticato.
     *
     * @responseField userid L'ID dell'utente.
     * @responseField privilegio Il livello di privilegio.
     * @responseField nome_cognome Nome e cognome.
     */
    public function whoami(Request $request): JsonResponse
    {
        $user = $request->user()->load('utente');

        return response()->json([
            'userid' => $user->utente->id,
            'privilegio' => $user->privilegi,
            'nome_cognome' => $user->utente->nome.' '.$user->utente->cognome,
        ]);
    }
}
