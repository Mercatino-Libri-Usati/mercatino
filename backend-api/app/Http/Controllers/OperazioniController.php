<?php

namespace App\Http\Controllers;

use App\Models\Operazione;
use Illuminate\Http\JsonResponse;

class OperazioniController extends Controller
{
    public function deleteOperazione(int|string $id): JsonResponse
    {
        $operazione = Operazione::find($id);
        if (! $operazione) {
            return response()->json(['message' => 'Operazione non trovata'], 404);
        }
        if ($operazione->tipo !== 'manuale') {
            return response()->json(['message' => 'Solo le operazioni manuali possono essere eliminate'], 403);
        }

        $operazione->delete();

        return response()->json(['message' => 'Operazione eliminata con successo']);
    }

    public function listAll(): JsonResponse
    {
        $operazioni = Operazione::orderBy('data', 'desc')->get();

        return response()->json($operazioni);
    }

    public function calcolaCassa(): JsonResponse
    {
        $bilancioGiornaliero = Operazione::selectRaw('DATE(data) as giorno, SUM(importo) as bilancio')
            ->groupBy('giorno')
            ->orderBy('giorno', 'desc')
            ->get();

        $bilancio = Operazione::sum('importo');

        return response()->json([
            'bilancio' => (float) $bilancio,
            'bilancio_giornaliero' => $bilancioGiornaliero,
        ]);

    }

    public function aggiungiOperazioneManuale(): JsonResponse
    {
        $validated = request()->validate([
            'importo' => 'required|numeric',
            'causale' => 'nullable|string|max:255',
        ]);

        $validated['tipo'] = 'manuale';

        $operazione = Operazione::aggiungi($validated);

        return response()->json($operazione, 200);
    }
}
