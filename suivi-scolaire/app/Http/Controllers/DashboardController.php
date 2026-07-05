<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Services\MoyenneService;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $annee = config('app.annee_scolaire');
        $trimestre = $request->trimestre ?? 1;

        // Total élèves
        $totalEleves = Eleve::where('annee_scolaire', $annee)
                            ->where('statut', 'actif')
                            ->count();

        // Total classes
        $totalClasses = Classe::where('annee_scolaire', $annee)->count();

        // Frais attendus
        $fraisAttendus = Eleve::where('eleves.annee_scolaire', $annee)
                      ->where('eleves.statut', 'actif')
                      ->join('classes', 'eleves.classe_id', '=', 'classes.id')
                      ->sum('classes.frais_scolarite');

        // Frais collectés
        $fraisCollectes = Paiement::whereHas('eleve', function($q) use ($annee) {
                              $q->where('annee_scolaire', $annee);
                          })->sum('montant');

        // Reste total
        $resteTotal = max(0, $fraisAttendus - $fraisCollectes);

        // Taux de recouvrement
        $taux = $fraisAttendus > 0
                ? round(($fraisCollectes / $fraisAttendus) * 100, 1)
                : 0;

        // Stats par classe
        $statsClasses = Classe::where('annee_scolaire', $annee)
                              ->withCount(['eleves as nb_eleves' => function($q) use ($annee) {
                                  $q->where('statut', 'actif')
                                    ->where('annee_scolaire', $annee);
                              }])
                              ->get()
                              ->map(function($classe) {
                                  $collectes = DB::table('paiements')
                                      ->join('eleves', 'paiements.eleve_id', '=', 'eleves.id')
                                      ->where('eleves.classe_id', $classe->id)
                                      ->sum('paiements.montant');
                                  $classe->frais_collectes = $collectes;
                                  $classe->frais_attendus = $classe->nb_eleves * $classe->frais_scolarite;
                                  return $classe;
                              });

        // Élèves en retard de paiement
        $elevesImpayes = Eleve::where('annee_scolaire', $annee)
                              ->where('statut', 'actif')
                              ->with(['classe', 'paiements'])
                              ->get()
                              ->filter(fn($e) => $e->reste_a_payer > 0)
                              ->take(10);

        // Paiements récents
        $paiementsRecents = Paiement::with(['eleve.classe'])
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)
                                    ->get();

        // Aperçu pédagogique : nombre de matières et moyenne générale par
        // classe, pour le trimestre sélectionné.
        $statsPedagogiques = Classe::where('annee_scolaire', $annee)
            ->orderBy('niveau')
            ->orderBy('nom')
            ->get()
            ->map(function ($classe) use ($annee, $trimestre) {
                $classe->nb_matieres = Matiere::where('classe_id', $classe->id)
                                              ->where('is_active', true)
                                              ->count();

                $eleves = Eleve::where('classe_id', $classe->id)
                               ->where('annee_scolaire', $annee)
                               ->where('statut', 'actif')
                               ->get();

                $moyennes = $eleves
                    ->map(fn($eleve) => MoyenneService::moyenneEleve($eleve, $trimestre, $annee))
                    ->filter(fn($m) => $m !== null);

                $classe->moyenne_generale = $moyennes->isNotEmpty()
                    ? round($moyennes->avg(), 2)
                    : null;

                return $classe;
            });

        return view('admin.dashboard', compact(
            'totalEleves', 'totalClasses',
            'fraisAttendus', 'fraisCollectes',
            'resteTotal', 'taux',
            'statsClasses', 'elevesImpayes',
            'paiementsRecents', 'annee',
            'statsPedagogiques', 'trimestre'
        ));
    }
}