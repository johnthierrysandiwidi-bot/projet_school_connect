<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EleveController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ImpayeController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\DevoirController;
use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\MatiereController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\PassageAnneeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/home', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
         ->name('login');
    Route::post('/login', [AuthController::class, 'login'])
         ->middleware('throttle:5,1');

    // Mot de passe oublié
    Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'showForgotForm'])
         ->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'sendResetLink'])
         ->middleware('throttle:5,1')
         ->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [PasswordResetController::class, 'showResetForm'])
         ->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [PasswordResetController::class, 'reset'])
         ->middleware('throttle:5,1')
         ->name('password.update');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [AuthController::class, 'dashboard'])
         ->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])
         ->name('logout');

    // Changer mon propre mot de passe : accessible à tout utilisateur
    // connecté, quel que soit son rôle.
    Route::get('/profil/mot-de-passe', [ProfileController::class, 'editPassword'])
         ->name('profile.password.edit');
    Route::put('/profil/mot-de-passe', [ProfileController::class, 'updatePassword'])
         ->name('profile.password.update');

    // Notes & bulletins : accessibles au Gestionnaire et à l'Enseignant.
    // NoteController restreint en interne un Enseignant aux élèves de sa
    // propre classe (voir index/store/classement/bulletin).
    Route::middleware('role:gestionnaire,enseignant')->group(function () {
        Route::get('/notes', [NoteController::class, 'index'])
             ->name('notes.index');
        Route::post('/notes', [NoteController::class, 'store'])
             ->name('notes.store');
        Route::get('/notes/classement', [NoteController::class, 'classement'])
             ->name('notes.classement');
        Route::get('/notes/bulletin/{eleve}', [NoteController::class, 'bulletin'])
             ->name('notes.bulletin');

        // Cahier de notes / devoirs : chaque enseignant compose ses devoirs
        // pour sa classe ; DevoirController restreint en interne un
        // Enseignant aux devoirs de sa propre classe.
        Route::get('/devoirs', [DevoirController::class, 'index'])
             ->name('devoirs.index');
        Route::get('/devoirs/create', [DevoirController::class, 'create'])
             ->name('devoirs.create');
        Route::post('/devoirs', [DevoirController::class, 'store'])
             ->name('devoirs.store');
        Route::get('/devoirs/{devoir}/notes', [DevoirController::class, 'notes'])
             ->name('devoirs.notes');
        Route::post('/devoirs/{devoir}/notes', [DevoirController::class, 'storeNotes'])
             ->name('devoirs.notes.store');
        Route::delete('/devoirs/{devoir}', [DevoirController::class, 'destroy'])
             ->name('devoirs.destroy');

        // Absences : feuille de présence quotidienne par classe. Mêmes
        // règles d'accès que les notes (un Enseignant ne gère que SA classe).
        Route::get('/absences', [AbsenceController::class, 'index'])
             ->name('absences.index');
        Route::post('/absences', [AbsenceController::class, 'store'])
             ->name('absences.store');
        Route::get('/absences/historique', [AbsenceController::class, 'historique'])
             ->name('absences.historique');

        // Annonces : un Enseignant ne peut publier que pour sa propre
        // classe (vérifié dans AnnonceController@store), un Gestionnaire
        // peut cibler toute l'école ou une classe précise.
        Route::get('/annonces', [AnnonceController::class, 'index'])
             ->name('annonces.index');
        Route::get('/annonces/create', [AnnonceController::class, 'create'])
             ->name('annonces.create');
        Route::post('/annonces', [AnnonceController::class, 'store'])
             ->name('annonces.store');
        Route::delete('/annonces/{annonce}', [AnnonceController::class, 'destroy'])
             ->name('annonces.destroy');
    });

    // Tout ce qui suit est réservé au Gestionnaire : gestion administrative,
    // financière et des comptes enseignants.
    Route::middleware('role:gestionnaire')->group(function () {

        // Élèves
        Route::get('/eleves', [EleveController::class, 'index'])
             ->name('eleves.index');
        Route::get('/eleves/create', [EleveController::class, 'create'])
             ->name('eleves.create');
        Route::post('/eleves', [EleveController::class, 'store'])
             ->name('eleves.store');
        Route::get('/eleves/{eleve}/edit', [EleveController::class, 'edit'])
             ->name('eleves.edit');
        Route::put('/eleves/{eleve}', [EleveController::class, 'update'])
             ->name('eleves.update');
        Route::delete('/eleves/{eleve}', [EleveController::class, 'destroy'])
             ->name('eleves.destroy');
        Route::get('/eleves/{eleve}', [EleveController::class, 'show'])
             ->name('eleves.show');

        // Paiements
        Route::get('/paiements', [PaiementController::class, 'index'])
             ->name('paiements.index');
        Route::get('/paiements/create', [PaiementController::class, 'create'])
             ->name('paiements.create');
        Route::post('/paiements', [PaiementController::class, 'store'])
             ->name('paiements.store');
        Route::get('/paiements/{paiement}', [PaiementController::class, 'show'])
             ->name('paiements.show');
        Route::get('/paiements/{paiement}/recu', [PaiementController::class, 'recu'])
             ->name('paiements.recu');
        Route::get('/paiements/{paiement}/recu-pdf', [PaiementController::class, 'recuPdf'])
             ->name('paiements.recu-pdf');

        // Impayés : vue dédiée, filtrable, de tous les élèves en retard
        Route::get('/impayes', [ImpayeController::class, 'index'])
             ->name('impayes.index');

        // Classes
        Route::get('/classes', [ClasseController::class, 'index'])
             ->name('classes.index');
        Route::get('/classes/create', [ClasseController::class, 'create'])
             ->name('classes.create');
        Route::post('/classes', [ClasseController::class, 'store'])
             ->name('classes.store');
        Route::get('/classes/{classe}', [ClasseController::class, 'show'])
             ->name('classes.show');
        Route::get('/classes/{classe}/edit', [ClasseController::class, 'edit'])
             ->name('classes.edit');
        Route::put('/classes/{classe}', [ClasseController::class, 'update'])
             ->name('classes.update');
             

        // Matières : page dédiée, en plus de la gestion intégrée aux
        // formulaires de classe (les deux pointent vers le même contrôleur).
        Route::get('/matieres', [MatiereController::class, 'index'])
             ->name('matieres.index');
        Route::get('/matieres/create', [MatiereController::class, 'create'])
             ->name('matieres.create');
        Route::post('/matieres', [MatiereController::class, 'store'])
             ->name('matieres.store');
        Route::get('/matieres/{matiere}/edit', [MatiereController::class, 'edit'])
             ->name('matieres.edit');
        Route::put('/matieres/{matiere}', [MatiereController::class, 'update'])
             ->name('matieres.update');
        Route::delete('/matieres/{matiere}', [MatiereController::class, 'destroy'])
             ->name('matieres.destroy');

        // Enseignants
        Route::get('/enseignants', [EnseignantController::class, 'index'])
             ->name('enseignants.index');
        Route::get('/enseignants/create', [EnseignantController::class, 'create'])
             ->name('enseignants.create');
        Route::post('/enseignants', [EnseignantController::class, 'store'])
             ->name('enseignants.store');
        Route::get('/enseignants/{enseignant}/edit', [EnseignantController::class, 'edit'])
             ->name('enseignants.edit');
        Route::put('/enseignants/{enseignant}', [EnseignantController::class, 'update'])
             ->name('enseignants.update');
        Route::delete('/enseignants/{enseignant}', [EnseignantController::class, 'destroy'])
             ->name('enseignants.destroy');

        // Comptes parents (accès à l'application mobile)
        Route::get('/parents', [ParentController::class, 'index'])
             ->name('parents.index');
        Route::get('/parents/create', [ParentController::class, 'create'])
             ->name('parents.create');
        Route::post('/parents', [ParentController::class, 'store'])
             ->name('parents.store');
        Route::get('/parents/{parent}/edit', [ParentController::class, 'edit'])
             ->name('parents.edit');
        Route::put('/parents/{parent}', [ParentController::class, 'update'])
             ->name('parents.update');
        Route::delete('/parents/{parent}', [ParentController::class, 'destroy'])
             ->name('parents.destroy');

        // Paramètres de l'établissement (année scolaire active)
        Route::get('/parametres', [ParametreController::class, 'index'])
             ->name('parametres.index');
        Route::put('/parametres', [ParametreController::class, 'update'])
             ->name('parametres.update');

        // Passage à l'année scolaire suivante
        Route::get('/passage-annee', [PassageAnneeController::class, 'index'])
             ->name('passage-annee.index');
        Route::post('/passage-annee', [PassageAnneeController::class, 'executer'])
             ->name('passage-annee.executer');
    });
});

