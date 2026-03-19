<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\LibriController;
use App\Http\Controllers\OperazioniController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RestituzioneController;
use App\Http\Controllers\RicevuteController;
use App\Http\Controllers\RicevuteDownloadController;
use App\Http\Controllers\RitiroController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserListController;
use App\Http\Controllers\UtilController;
use App\Http\Controllers\VenditaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/stats', [StatsController::class, 'getStats'])->middleware('auth:sanctum');

Route::get('/adozioni', [CatalogoController::class, 'index'])->middleware(['auth:sanctum', 'etag']);
Route::patch('/catalogo/{id_catalogo}/prezzo', [CatalogoController::class, 'updatePrezzo'])->middleware('auth:sanctum');

Route::get('/util/isAccettato/{isbn}', [UtilController::class, 'isAccettato'])->middleware('auth:sanctum');
Route::get('/util/isPrenotabile/{isbn}', [UtilController::class, 'isPrenotabile'])->middleware('auth:sanctum');
Route::get('/util/isVendibile/{numeroAnnuo}', [UtilController::class, 'isVendibile'])->middleware('auth:sanctum');

Route::get('/ricevute/ritiro/{id}', [RitiroController::class, 'showRitiro'])->middleware('auth:sanctum', 'etag');
Route::post('/ricevute/ritiro', [RitiroController::class, 'addRitiro'])->middleware('auth:sanctum');
Route::get('/ricevute/vendita/{id}', [VenditaController::class, 'showVendita'])->middleware('auth:sanctum', 'etag');
Route::post('/ricevute/vendita', [VenditaController::class, 'addVendita'])->middleware('auth:sanctum');

Route::get('/ricevute/restituzione/preview', [RestituzioneController::class, 'previewRestituzione'])->middleware(['auth:sanctum', 'etag']);
Route::get('/ricevute/restituzione/{id}', [RestituzioneController::class, 'showRestituzione'])->middleware(['auth:sanctum', 'etag']);
Route::post('/ricevute/restituzione', [RestituzioneController::class, 'addRestituzione'])->middleware('auth:sanctum');

Route::get('/ricevute/download', [RicevuteDownloadController::class, 'download'])->name('ricevute.download')->middleware(['auth:sanctum', 'etag']);
Route::get('/ricevute', [RicevuteController::class, 'index'])->middleware('auth:sanctum', 'etag');

Route::post('/registra', [RegisterController::class, 'register'])->middleware('throttle:5,1');
Route::post('/imposta-password', [RegisterController::class, 'completeRegistration']);
Route::post('/richiedi-link-password', [RegisterController::class, 'requestPasswordLink']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:15,1');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/whoami', function (Request $request) {
    $user = $request->user()->load('utente');

    return response()->json([
        'userid' => $user->utente->ID,
        'privilegio' => $user->privilegi,
        'nome_cognome' => $user->utente->nome.' '.$user->utente->cognome,
    ]);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {

    return $request->user()->load('utente');
});
Route::middleware(['auth:sanctum', 'etag'])->get('/elenco-utenti-ridotto', [UserListController::class, 'indexRidotto']);
Route::middleware(['auth:sanctum', 'etag'])->get('/elenco-utenti', [UserListController::class, 'index']);
Route::middleware('auth:sanctum')->get('/utente/{id}', [UserListController::class, 'show']);
Route::middleware('auth:sanctum')->patch('/utente/{id}', [UserListController::class, 'updateUtente']);

Route::middleware('auth:sanctum')->patch('/libro/{id}/note', [LibriController::class, 'modificaNote']);
Route::middleware('auth:sanctum')->patch('/libro/{id}/prezzo', [LibriController::class, 'modificaPrezzo']);
Route::middleware('auth:sanctum')->get('/libri', [LibriController::class, 'index']);

Route::middleware('auth:sanctum')->get('/operazioni', [OperazioniController::class, 'listAll']);
Route::middleware('auth:sanctum')->get('/cassa', [OperazioniController::class, 'calcolaCassa']);
Route::middleware('auth:sanctum')->delete('/operazione/{id}', [OperazioniController::class, 'deleteOperazione']);
Route::middleware('auth:sanctum')->post('/operazione/manuale', [OperazioniController::class, 'aggiungiOperazioneManuale']);
