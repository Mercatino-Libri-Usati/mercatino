<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $login = $request->input('login');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'mail' : 'nickname';

        if ($field === 'mail') {
            $user = User::whereHas('utente', function ($query) use ($login) {
                $query->where('mail', $login);
            })->first();
        } else {
            $user = User::where('nickname', $login)->first();
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
            'userid' => $user->utente->ID,
            'privilegio' => $user->privilegi,
            'nome_cognome' => $user->utente->nome.' '.$user->utente->cognome,
        ])->withCookie($cookie);
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete(); // Elimina tutti i token dell'utente
        }
        $cookie = cookie()->forget('auth_token');

        return response()->json(['message' => 'Logout effettuato con successo.'])->withCookie($cookie);
    }
}
