<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\LibriController;
use App\Http\Controllers\OperazioniController;
use App\Http\Controllers\PrenotazioneController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RestituzioneController;
use App\Http\Controllers\RicevuteController;
use App\Http\Controllers\RicevuteDownloadController;
use App\Http\Controllers\RitiroController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserListController;
use App\Http\Controllers\UtilController;
use App\Http\Controllers\VenditaController;
use Illuminate\Support\Facades\Route;

Route::post('/registra', [RegisterController::class, 'register'])->middleware('throttle:5,1');
Route::post('/imposta-password', [RegisterController::class, 'completeRegistration']);
Route::post('/richiedi-link-password', [RegisterController::class, 'requestPasswordLink']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:15,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/whoami', [AuthController::class, 'whoami']);
});

Route::middleware(['auth:sanctum', 'privilege:2'])->group(function () {
    Route::get('/stats', [StatsController::class, 'getStats']);
    Route::get('/adozioni', [CatalogoController::class, 'index'])->middleware('etag');
    Route::patch('/catalogo/{id_catalogo}/prezzo', [CatalogoController::class, 'updatePrezzo']);
    Route::get('/util/isAccettato/{isbn}', [UtilController::class, 'isAccettato']);
    Route::get('/util/isPrenotabile/{isbn}', [UtilController::class, 'isPrenotabile']);
    Route::get('/util/isVendibile/{numeroAnnuo}', [UtilController::class, 'isVendibile']);
    Route::get('/ricevute/ritiro/{id}', [RitiroController::class, 'showRitiro'])->middleware('etag');
    Route::post('/ricevute/ritiro', [RitiroController::class, 'addRitiro']);
    Route::post('/ricevute/prenotazione', [PrenotazioneController::class, 'addPrenotazione']);
    Route::get('/ricevute/prenotazione/{id}', [PrenotazioneController::class, 'showPrenotazione'])->middleware('etag');
    Route::post('/prenotazioni/rimuoviLibro', [PrenotazioneController::class, 'rimuoviLibroPrenotazione']);
    Route::post('/prenotazioni/scambiaLibro', [PrenotazioneController::class, 'scambiaLibroPrenotazione']);
    Route::get('/prenotazioni', [PrenotazioneController::class, 'indexPrenotazioni'])->middleware('etag');
    Route::get('/prenotazioni/libriScambiabili/{isbn}', [PrenotazioneController::class, 'libriScambiabili']);
    Route::get('/ricevute/vendita/{id}', [VenditaController::class, 'showVendita'])->middleware('etag');
    Route::post('/ricevute/vendita', [VenditaController::class, 'addVendita']);
    Route::get('/ricevute/restituzione/preview', [RestituzioneController::class, 'previewRestituzione'])->middleware('etag');
    Route::get('/ricevute/restituzione/{id}', [RestituzioneController::class, 'showRestituzione'])->middleware('etag');
    Route::post('/ricevute/restituzione', [RestituzioneController::class, 'addRestituzione']);
    Route::get('/ricevute/download', [RicevuteDownloadController::class, 'download'])->name('ricevute.download')->middleware('etag');
    Route::get('/ricevute', [RicevuteController::class, 'index'])->middleware('etag');
    Route::get('/elenco-utenti-ridotto', [UserListController::class, 'indexRidotto'])->middleware('etag');
    Route::get('/elenco-utenti', [UserListController::class, 'index'])->middleware('etag');
    Route::get('/utente/{id}', [UserListController::class, 'show']);
    Route::patch('/utente/{id}', [UserListController::class, 'updateUtente']);
    Route::patch('/libro/{id}/note', [LibriController::class, 'modificaNote']);
    Route::patch('/libro/{id}/prezzo', [LibriController::class, 'modificaPrezzo']);
    Route::get('/libri', [LibriController::class, 'index']);
    Route::get('/operazioni', [OperazioniController::class, 'listAll']);
    Route::get('/cassa', [OperazioniController::class, 'calcolaCassa']);
    Route::delete('/operazione/{id}', [OperazioniController::class, 'deleteOperazione']);
    Route::post('/operazione/manuale', [OperazioniController::class, 'aggiungiOperazioneManuale']);
});

Route::middleware(['auth:sanctum', 'privilege:3'])->group(function () {
    Route::post('/imposta-privilegi', [UserListController::class, 'impostaPrivilegi']);
});
