<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateParametresRequest;
use App\Models\Classe;
use App\Models\Parametre;

class ParametreController extends Controller
{
    public function index()
    {
        $anneeActive = config('app.annee_scolaire');
        $nomEcole = config('app.nom_ecole');
        $adresseEcole = config('app.adresse_ecole');
        $telephoneEcole = config('app.telephone_ecole');

        $anneesConnues = Classe::distinct()
                               ->orderByDesc('annee_scolaire')
                               ->pluck('annee_scolaire');

        return view('admin.parametres.index', compact(
            'anneeActive', 'anneesConnues', 'nomEcole', 'adresseEcole', 'telephoneEcole'
        ));
    }

    public function update(UpdateParametresRequest $request)
    {
        Parametre::ecrire('annee_scolaire_active', $request->annee_scolaire_active);
        Parametre::ecrire('nom_ecole', $request->nom_ecole);
        Parametre::ecrire('adresse_ecole', $request->adresse_ecole ?? '');
        Parametre::ecrire('telephone_ecole', $request->telephone_ecole ?? '');

        return redirect()
            ->route('parametres.index')
            ->with('success', "Les paramètres ont été mis à jour.");
    }
}
