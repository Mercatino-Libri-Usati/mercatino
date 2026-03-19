<?php

namespace App\Http\Controllers;

use App\Mail\PasswordLink;
use App\Models\User;
use App\Models\Utente;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cognome' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:utenti,mail',
            'telefono' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            Utente::create([
                'nome' => $request->nome,
                'cognome' => $request->cognome,
                'mail' => $request->email,
                'telefono' => $request->telefono,
                'scuola' => 1,
                'ID_registro' => 1,
                'data' => now()->format('Y-m-d'),
            ]);

            DB::commit();

            return response()->json(null, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Si è verificato un errore durante la registrazione.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function requestPasswordLink(Request $request): \Illuminate\Http\JsonResponse
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
        DB::table('pwd_request')->insert(
            [
                'id_utenti' => $utente->ID,
                'token' => $token,
                'date_req' => now(),
            ]
        );
        $utente->save();

        // Invia email con link per impostare nuova password
        Mail::to($utente->mail)->send(new PasswordLink($token, $utente->mail));

        return response()->json([
            'message' => 'Se l\'email esiste nel nostro sistema, riceverai un link per reimpostare la password.',
        ]);
    }

    public function completeRegistration(Request $request): \Illuminate\Http\JsonResponse
    {

        try {
            $request->validate([
                'token' => 'required|string',
                'password' => 'required|string|min:6',
            ]);

            $pwdRequest = DB::table('pwd_request')->where('token', $request->token)->first();

            if (! $pwdRequest) {
                return response()->json([
                    'message' => 'Token di verifica non valido.',
                ], Response::HTTP_BAD_REQUEST);
            }

            if (! now()->diffInMinutes($pwdRequest->date_req) > 1440) {
                return response()->json([
                    'message' => 'Il token di verifica è scaduto.',
                ], Response::HTTP_BAD_REQUEST);
            }

            DB::beginTransaction();

            // Crea un nuovo record in users se non esiste già
            $user = User::where('ID_utenti', $pwdRequest->id_utenti);
            if (! $user->exists()) {
                // Create real User
                $user = User::create([
                    'ID_utenti' => $pwdRequest->id_utenti,
                    'nickname' => '', // per legacy login (non usato)
                    'password' => md5($request->password),
                    'ID_registro' => 1, // legacy
                    'sede' => 1, // legacy
                    'privilegi' => 1, // utente base
                    'attivo' => 1, // legacy
                ]);
            } else {
                $user = $user->first();
                // Aggiorna la password esistente
                $user->password = md5($request->password);
                $user->save();
            }

            DB::commit();
            // Elimina la richiesta di password dopo l'uso (dopo il commit per evitare problemi di rollback)
            DB::table('pwd_request')->where('id_utenti', $pwdRequest->id_utenti)->delete();

            return response()->json([
                'message' => 'Registrazione completata con successo. Ora puoi effettuare il login.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Errore di validazione',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Errore durante la registrazione: ' . $e->getMessage());
            return response()->json([
                'message' => 'Si è verificato un errore durante la registrazione.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
