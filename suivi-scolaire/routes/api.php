<?php

use App\Http\Controllers\Api\AbsenceController;
use App\Http\Controllers\Api\AnnonceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EnfantController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\PaiementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Application mobile Parent
|--------------------------------------------------------------------------
|
| Consommées par l'application Android. Authentification par jeton
| Sanctum (en-tête "Authorization: Bearer <token>"), obtenu via /login.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Connexion — publique, limitée à 5 tentatives/minute par IP.
Route::post('/login', [AuthController::class, 'login'])
     ->middleware('throttle:5,1');

// Tout ce qui suit nécessite un jeton valide ET un compte de rôle "parent".
Route::middleware(['auth:sanctum', 'role:parent'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/password', [AuthController::class, 'changePassword']);

    // Enfants du parent connecté
    Route::get('/enfants', [EnfantController::class, 'index']);
    Route::get('/enfants/{eleve}', [EnfantController::class, 'show']);

    // Notes & moyennes
    Route::get('/enfants/{eleve}/notes', [NoteController::class, 'index']);

    // Paiements
    Route::get('/enfants/{eleve}/paiements', [PaiementController::class, 'index']);
    Route::get('/enfants/{eleve}/paiements/{paiement}/recu', [PaiementController::class, 'recu']);

    // Absences
    Route::get('/enfants/{eleve}/absences', [AbsenceController::class, 'index']);

    // Annonces & notifications
    Route::get('/annonces', [AnnonceController::class, 'index']);
    Route::post('/annonces/{annonce}/lue', [AnnonceController::class, 'marquerLue']);
});
