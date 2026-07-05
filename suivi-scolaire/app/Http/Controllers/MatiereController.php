<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMatiereRequest;
use App\Http\Requests\UpdateMatiereRequest;
use App\Models\Classe;
use App\Models\Matiere;
use Illuminate\Http\Request;

class MatiereController extends Controller
{
    // Liste de toutes les matières de l'année en cours, filtrable par classe.
    public function index(Request $request)
    {
        $annee = config('app.annee_scolaire');

        $classes = Classe::where('annee_scolaire', $annee)->orderBy('niveau')->orderBy('nom')->get();

        $query = Matiere::whereIn('classe_id', $classes->pluck('id'))->with('classe');

        if ($request->classe_id) {
            $query->where('classe_id', $request->classe_id);
        }

        $matieres = $query->get()
                          ->sortBy([
                              fn ($m) => $m->classe->niveau ?? '',
                              fn ($m) => $m->nom,
                          ]);

        return view('admin.matieres.index', compact('matieres', 'classes', 'annee'));
    }

    // Formulaire de création
    public function create()
    {
        $annee = config('app.annee_scolaire');
        $classes = Classe::where('annee_scolaire', $annee)->orderBy('niveau')->orderBy('nom')->get();

        return view('admin.matieres.create', compact('classes'));
    }

    // Enregistrer une nouvelle matière
    public function store(StoreMatiereRequest $request)
    {
        $validated = $request->validated();
        $validated['is_active'] = true;

        $matiere = Matiere::create($validated);

        return redirect()
            ->route('matieres.index')
            ->with('success', "La matière « {$matiere->nom} » a été créée !");
    }

    // Formulaire de modification
    public function edit(Matiere $matiere)
    {
        $annee = config('app.annee_scolaire');
        $classes = Classe::where('annee_scolaire', $annee)->orderBy('niveau')->orderBy('nom')->get();

        return view('admin.matieres.edit', compact('matiere', 'classes'));
    }

    // Enregistrer les modifications
    public function update(UpdateMatiereRequest $request, Matiere $matiere)
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        $matiere->update($validated);

        return redirect()
            ->route('matieres.index')
            ->with('success', "La matière « {$matiere->nom} » a été mise à jour !");
    }

    // Supprimer une matière
    public function destroy(Matiere $matiere)
    {
        $nom = $matiere->nom;
        $matiere->delete();

        return redirect()
            ->back()
            ->with('success', "La matière « {$nom} » a été supprimée !");
    }
}
