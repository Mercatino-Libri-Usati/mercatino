<?php

namespace App\Http\Controllers;

use App\Mail\PasswordLink;
use App\Models\Credenziali;
use App\Models\RichiestaPassword;
use App\Models\Utente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @group Registrazione
 */
class RegisterController extends Controller
{
    /**
     * Registrazione utente
     *
     * Registra un nuovo utente nel sistema.
     *
     * @bodyParam nome string required Il nome dell'utente. No-example
     * @bodyParam cognome string required Il cognome dell'utente. No-example
     * @bodyParam email string required L'email dell'utente. No-example
     * @bodyParam telefono string required Il telefono dell'utente. No-example
     *
     * @responseField message Messaggio di conferma registrazione.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cognome' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:utenti,mail',
            'telefono' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request): void {
                Utente::create([
                    'nome' => $request->nome,
                    'cognome' => $request->cognome,
                    'mail' => $request->email,
                    'telefono' => $request->telefono,
                    'scuola' => 1,
                    'id_registro' => 1,
                    'data' => now()->format('Y-m-d'),
                ]);
            });

            return response()->json(null, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Si è verificato un errore durante la registrazione.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Richiesta link password
     *
     * Invia un link per impostare o reimpostare la password all'indirizzo email specificato.
     *
     * @bodyParam email string required L'email dell'utente. No-example
     *
     * @responseField message Messaggio di esito, non rivela se l'email esiste per sicurezza.
     */
    public function requestPasswordLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $utente = Utente::where('mail', $request->email)->first();

        if (! $utente) { // Per sicurezza, non rivelare se l'email esiste o meno
            return response()->json([
                'message' => 'Se l\'email esiste nel nostro sistema, riceverai un link per reimpostare la password.',
            ]);
        }

        $token = Str::random(60);
        RichiestaPassword::create([
            'id_utente' => $utente->id,
            'token' => $token,
            'data' => now(),
        ]);
        $utente->save();

        if (env('APP_ENV') !== 'production') {
            return response()->json([
                'message' => 'Se l\'email esiste nel nostro sistema, riceverai un link per reimpostare la password.',
                'debug_link' => env('FRONTEND_URL', 'http://localhost:5173').'/imposta-password?token='.$token.'&email='.urlencode($utente->mail),
            ]);
        }

        // Invia email con link per impostare nuova password
        Mail::to($utente->mail)->send(new PasswordLink($token, $utente->mail));

        return response()->json([
            'message' => 'Se l\'email esiste nel nostro sistema, riceverai un link per reimpostare la password.',
        ]);
    }

    /**
     * Completa registrazione
     *
     * Imposta o reimposta la password utilizzando il token ricevuto via email.
     *
     * @bodyParam email string required L'email dell'utente. No-example
     * @bodyParam token string required Il token di sicurezza ricevuto via email. No-example
     * @bodyParam password string required La nuova password. No-example
     * @bodyParam password_confirmation string required Conferma della nuova password. No-example
     * @bodyParam nickname string required Il nickname scelto dall'utente. No-example
     *
     * @responseField message Messaggio di successo o errore.
     */
    public function completeRegistration(Request $request): JsonResponse
    {

        try {
            $request->validate([
                'token' => 'required|string',
                'password' => 'required|string|min:6',
            ]);

            $pwdRequest = RichiestaPassword::where('token', $request->token)->first();

            if (! $pwdRequest) {
                return response()->json([
                    'message' => 'Token di verifica non valido.',
                ], Response::HTTP_BAD_REQUEST);
            }

            if (now()->diffInMinutes($pwdRequest->data) > 1440) {
                return response()->json([
                    'message' => 'Il token di verifica è scaduto.',
                ], Response::HTTP_BAD_REQUEST);
            }

            DB::transaction(function () use ($pwdRequest, $request): void {
                Credenziali::updateOrCreate(
                    ['id_utente' => $pwdRequest->id_utente],
                    [
                        'id_utente' => $pwdRequest->id_utente,
                        'nickname' => '', // per legacy login (non usato)
                        'password' => md5($request->password),
                        'id_registro' => 1, // legacy
                        'sede' => 1, // legacy
                        'privilegi' => 1, // utente base
                        'attivo' => 1, // legacy
                    ]
                );

                $pwdRequest->delete();
            });

            return response()->json([
                'message' => 'Registrazione completata con successo. Ora puoi effettuare il login.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Errore di validazione',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Si è verificato un errore durante la registrazione.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
