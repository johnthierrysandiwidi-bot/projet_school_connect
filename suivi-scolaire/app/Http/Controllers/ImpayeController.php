<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Eleve;
use Illuminate\Http\Request;

class ImpayeController extends Controller
{
    // Liste complète des élèves en situation d'impayé, avec filtres.
    public function index(Request $request)
    {
        $annee = config('app.annee_scolaire');

        $query = Eleve::where('annee_scolaire', $annee)
                      ->where('statut', 'actif')
                      ->with(['classe', 'paiements']);

        if ($request->classe_id) {
            $query->where('classe_id', $request->classe_id);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('prenom', 'like', '%' . $request->search . '%')
                  ->orWhere('matricule', 'like', '%' . $request->search . '%');
            });
        }

        $impayes = $query->get()
                         ->filter(fn ($eleve) => $eleve->reste_a_payer > 0)
                         ->sortByDesc('reste_a_payer')
                         ->values();

        $montantTotalDu = $impayes->sum('reste_a_payer');
        $classes = Classe::where('annee_scolaire', $annee)->orderBy('niveau')->orderBy('nom')->get();

        return view('admin.impayes.index', compact(
            'impayes', 'montantTotalDu', 'classes', 'annee'
        ));
    }
}
