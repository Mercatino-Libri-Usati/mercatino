<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function getStats(): JsonResponse
    {
        $nLibri = DB::table('libron')->count();
        $venduti = DB::table('libron')->whereNotNull('id_vendita')->count();
        $nUtenti = DB::table('utenti')->count();
        $profitto = DB::table('log_operazioni')->sum('importo');
        $ricavo = DB::table('log_operazioni')->where('tipo', 'vendita')->sum('importo');

        return $this->successResponse([
            'n_libri' => $nLibri,
            'venduti' => $venduti,
            'n_utenti' => $nUtenti,
            'profitto' => $profitto,
            'ricavo' => $ricavo,
        ]);
    }
}
