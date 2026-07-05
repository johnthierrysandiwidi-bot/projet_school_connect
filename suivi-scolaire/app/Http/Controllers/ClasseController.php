<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClasseRequest;
use App\Http\Requests\UpdateClasseRequest;
use App\Models\Classe;
use App\Models\Matiere;

class ClasseController extends Controller
{
    // Liste des classes
    public function index()
    {
        $anneeScolaire = config('app.annee_scolaire');

        $classes = Classe::withCount(['eleves as nb_eleves' => function($q) use ($anneeScolaire) {
                              $q->where('statut', 'actif')
                                ->where('annee_scolaire', $anneeScolaire);
                          }])
                          ->with('matieres')
                          ->orderBy('niveau')
                          ->orderBy('nom')
                          ->get();

        $niveaux = ['CP1', 'CP2', 'CE1', 'CE2', 'CM1', 'CM2'];

        return view('admin.classes.index', compact('classes', 'niveaux', 'anneeScolaire'));
    }

    // Formulaire de création
    public function create()
    {
        // On propose toujours les 6 niveaux : une école peut avoir plusieurs
        // classes pour le même niveau (ex. CP1 A, CP1 B), distinguées par le
        // nom donné à chacune.
        $niveaux = ['CP1', 'CP2', 'CE1', 'CE2', 'CM1', 'CM2'];

        return view('admin.classes.create', compact('niveaux'));
    }

    // Enregistrer une classe
    public function store(StoreClasseRequest $request)
    {
        $validated = $request->validated();

        $validated['annee_scolaire'] = config('app.annee_scolaire');

        $classe = Classe::create($validated);

        // Créer les matières
        if ($request->matieres) {
            foreach ($request->matieres as $matiere) {
                if (!empty($matiere['nom'])) {
                    Matiere::create([
                        'nom'         => $matiere['nom'],
                        'coefficient' => $matiere['coefficient'] ?? 1,
                        'bareme'      => $matiere['bareme'] ?? 10,
                        'classe_id'   => $classe->id,
                        'is_active'   => true,
                    ]);
                }
            }
        }

        return redirect()
            ->route('classes.index')
            ->with('success', "La classe {$classe->nom} a été créée avec succès !");
    }

    // Voir une classe
    public function show(Classe $classe)
    {
        $classe->load(['eleves' => fn($q) => $q->where('statut', 'actif'), 'matieres']);
        return view('admin.classes.show', compact('classe'));
    }

    // Formulaire de modification
    public function edit(Classe $classe)
    {
        $classe->load('matieres');
        return view('admin.classes.edit', compact('classe'));
    }

    // Enregistrer les modifications
    public function update(UpdateClasseRequest $request, Classe $classe)
    {
        $validated = $request->validated();

        $classe->update([
            'nom'             => $validated['nom'],
            'frais_scolarite' => $validated['frais_scolarite'],
            'capacite_max'    => $validated['capacite_max'],
        ]);

        // Mettre à jour les matières existantes (uniquement celles de cette classe)
        if ($request->matieres) {
            foreach ($request->matieres as $id => $data) {
                $classe->matieres()->where('id', $id)->update([
                    'nom'         => $data['nom'],
                    'coefficient' => $data['coefficient'],
                    'bareme'      => $data['bareme'] ?? 10,
                ]);
            }
        }

        // Ajouter de nouvelles matières
        if ($request->nouvelles_matieres) {
            foreach ($request->nouvelles_matieres as $matiere) {
                if (!empty($matiere['nom'])) {
                    Matiere::create([
                        'nom'         => $matiere['nom'],
                        'coefficient' => $matiere['coefficient'] ?? 1,
                        'bareme'      => $matiere['bareme'] ?? 10,
                        'classe_id'   => $classe->id,
                        'is_active'   => true,
                    ]);
                }
            }
        }

        return redirect()
            ->route('classes.show', $classe)
            ->with('success', "La classe {$classe->nom} a été mise à jour !");
    }

}