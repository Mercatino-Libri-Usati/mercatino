<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

abstract class Controller
{
    /**
     * Messaggi di errore standardizzati
     */
    protected const MSG_DATI_NON_VALIDI = 'Dati non validi';

    protected const MSG_UTENTE_NON_TROVATO = 'Utente non trovato';

    protected const MSG_LIBRO_NON_TROVATO = 'Libro non trovato';

    protected const MSG_ERRORE_INTERNO = 'Errore interno del server';

    /**
     * Risposta JSON di successo
     */
    protected function successResponse(array $data = [], int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json($data, $status);
    }

    /**
     * Risposta JSON di errore
     */
    protected function errorResponse(string $message, int $status, array $errors = []): JsonResponse
    {
        $response = ['message' => $message];
        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Risposta per risorsa non trovata
     */
    protected function notFoundResponse(string $message = 'Risorsa non trovata'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Risposta per conflitto (es. risorsa già esistente)
     */
    protected function conflictResponse(string $message): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_CONFLICT);
    }

    /**
     * Risposta per risorsa creata
     */
    protected function createdResponse(array $data = []): JsonResponse
    {
        return $this->successResponse($data, Response::HTTP_CREATED);
    }

    /**
     * Verifica se un utente esiste
     */
    protected function userExists(int $userId): bool
    {
        return DB::table('utenti')->where('id', $userId)->exists();
    }

    /**
     * Calcola il prossimo numero progressivo annuale per una tabella
     */
    protected function getNextProgressivo(string $table, string $column, ?int $anno = null): int
    {
        $anno = $anno ?? now()->year;
        $max = DB::table($table)
            ->whereYear('data', $anno)
            ->max($column);

        return $max ? $max + 1 : 1;
    }
}
