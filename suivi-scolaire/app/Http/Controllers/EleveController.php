<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEleveRequest;
use App\Http\Requests\UpdateEleveRequest;
use App\Models\Eleve;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EleveController extends Controller
{
    // Liste des élèves
    public function index(Request $request)
    {
        $query = Eleve::with('classe')->orderBy('nom');

        // Recherche
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('prenom', 'like', '%' . $request->search . '%')
                  ->orWhere('matricule', 'like', '%' . $request->search . '%');
            });
        }

        // Filtre par classe
        if ($request->classe_id) {
            $query->where('classe_id', $request->classe_id);
        }

        // Filtre par sexe
        if ($request->sexe) {
            $query->where('sexe', $request->sexe);
        }

        $eleves = $query->paginate(20)->withQueryString();
        $classes = Classe::orderBy('niveau')->orderBy('nom')->get();

        return view('admin.eleves.index', compact('eleves', 'classes'));
    }

    // Formulaire d'inscription
    public function create()
    {
        $classes = Classe::orderBy('niveau')->orderBy('nom')->get();
        return view('admin.eleves.create', compact('classes'));
    }

    // Enregistrer un nouvel élève
    public function store(StoreEleveRequest $request)
    {
        $validated = $request->validated();

        $validated['matricule']      = Eleve::genererMatricule();
        $validated['annee_scolaire'] = config('app.annee_scolaire');

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')
                                          ->store('photos', 'public');
        }

        $eleve = Eleve::create($validated);

        return redirect()
            ->route('eleves.index')
            ->with('success', "L'élève {$eleve->nom} {$eleve->prenom} a été inscrit avec succès !");
    }

    // Voir le dossier d'un élève
    public function show(Eleve $eleve)
    {
        $eleve->load(['classe', 'paiements', 'notes.matiere']);
        return view('admin.eleves.show', compact('eleve'));
    }

    // Formulaire de modification
    public function edit(Eleve $eleve)
    {
        $classes = Classe::orderBy('niveau')->orderBy('nom')->get();
        return view('admin.eleves.edit', compact('eleve', 'classes'));
    }

    // Enregistrer les modifications
    public function update(UpdateEleveRequest $request, Eleve $eleve)
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            if ($eleve->photo) {
                Storage::disk('public')->delete($eleve->photo);
            }
            $validated['photo'] = $request->file('photo')
                                          ->store('photos', 'public');
        }

        $eleve->update($validated);

        return redirect()
            ->route('eleves.show', $eleve)
            ->with('success', "Les informations de {$eleve->nom} {$eleve->prenom} ont été mises à jour !");
    }

    // Supprimer un élève
    public function destroy(Eleve $eleve)
    {
        $nom = $eleve->nom . ' ' . $eleve->prenom;

        if ($eleve->photo) {
            Storage::disk('public')->delete($eleve->photo);
        }

        $eleve->delete();

        return redirect()
            ->route('eleves.index')
            ->with('success', "L'élève {$nom} a été supprimé.");
    }
}
