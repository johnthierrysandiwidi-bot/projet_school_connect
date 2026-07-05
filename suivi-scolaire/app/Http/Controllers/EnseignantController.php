<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnseignantRequest;
use App\Http\Requests\UpdateEnseignantRequest;
use App\Models\Absence;
use App\Models\Annonce;
use App\Models\Devoir;
use App\Models\Note;
use App\Models\User;
use App\Models\Classe;
use Illuminate\Support\Facades\Hash;

class EnseignantController extends Controller
{
    // Liste des enseignants
    public function index()
    {
        $enseignants = User::where('role', 'enseignant')
                           ->with('classe')
                           ->orderBy('name')
                           ->get();

        return view('admin.enseignants.index', compact('enseignants'));
    }

    // Formulaire de création
    public function create()
    {
        $classes = Classe::where('annee_scolaire', config('app.annee_scolaire'))
                         ->orderBy('niveau')->orderBy('nom')
                         ->get();

        return view('admin.enseignants.create', compact('classes'));
    }

    // Enregistrer un enseignant
    public function store(StoreEnseignantRequest $request)
    {
        $validated = $request->validated();

        $validated['role']      = 'enseignant';
        $validated['password']  = Hash::make($validated['password']);
        $validated['is_active'] = true;

        $enseignant = User::create($validated);

        return redirect()
            ->route('enseignants.index')
            ->with('success', "L'enseignant {$enseignant->name} a été créé avec succès !");
    }

    // Formulaire de modification
    public function edit(User $enseignant)
    {
        $this->assertEstEnseignant($enseignant);

        $classes = Classe::where('annee_scolaire', config('app.annee_scolaire'))
                         ->orderBy('niveau')->orderBy('nom')
                         ->get();

        return view('admin.enseignants.edit', compact('enseignant', 'classes'));
    }

    // Enregistrer les modifications
    public function update(UpdateEnseignantRequest $request, User $enseignant)
    {
        $this->assertEstEnseignant($enseignant);

        $validated = $request->validated();

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $enseignant->update($validated);

        return redirect()
            ->route('enseignants.index')
            ->with('success', "L'enseignant {$enseignant->name} a été mis à jour !");
    }

    // Supprimer un enseignant
    public function destroy(User $enseignant)
    {
        $this->assertEstEnseignant($enseignant);

        // Un enseignant qui a déjà saisi des notes, absences, devoirs ou
        // annonces ne peut pas être supprimé : ces enregistrements gardent
        // une référence (user_id) vers son compte, pour garder une trace de
        // qui a fait quoi. On invite à désactiver le compte à la place.
        $aDejaSaisiDesDonnees = Note::where('user_id', $enseignant->id)->exists()
            || Absence::where('user_id', $enseignant->id)->exists()
            || Devoir::where('user_id', $enseignant->id)->exists()
            || Annonce::where('user_id', $enseignant->id)->exists();

        if ($aDejaSaisiDesDonnees) {
            return redirect()
                ->route('enseignants.index')
                ->with('error', "Impossible de supprimer {$enseignant->name} : des notes, absences, devoirs ou annonces sont déjà enregistrés sous son nom. Désactivez plutôt son compte depuis « Modifier ».");
        }

        $nom = $enseignant->name;
        $enseignant->delete();

        return redirect()
            ->route('enseignants.index')
            ->with('success', "L'enseignant {$nom} a été supprimé !");
    }

    // Empêche cette page (réservée aux comptes Enseignant) d'agir sur un
    // compte Gestionnaire via un identifiant d'URL modifié.
    private function assertEstEnseignant(User $enseignant): void
    {
        abort_unless($enseignant->role === 'enseignant', 404);
    }
}
