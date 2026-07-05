<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParentRequest;
use App\Http\Requests\UpdateParentRequest;
use App\Models\Eleve;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ParentController extends Controller
{
    // Liste des comptes parents
    public function index()
    {
        $parents = User::where('role', 'parent')
                       ->with('enfants.classe')
                       ->orderBy('name')
                       ->get();

        return view('admin.parents.index', compact('parents'));
    }

    // Formulaire de création
    public function create()
    {
        $eleves = Eleve::where('annee_scolaire', config('app.annee_scolaire'))
                       ->where('statut', 'actif')
                       ->with('classe')
                       ->orderBy('nom')
                       ->get();

        return view('admin.parents.create', compact('eleves'));
    }

    // Enregistrer un compte parent
    public function store(StoreParentRequest $request)
    {
        $validated = $request->validated();

        $parent = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => 'parent',
            'is_active' => true,
        ]);

        $parent->enfants()->sync($validated['enfants']);

        return redirect()
            ->route('parents.index')
            ->with('success', "Le compte parent de {$parent->name} a été créé !");
    }

    // Formulaire de modification
    public function edit(User $parent)
    {
        $this->assertEstParent($parent);

        $eleves = Eleve::where('annee_scolaire', config('app.annee_scolaire'))
                       ->where('statut', 'actif')
                       ->with('classe')
                       ->orderBy('nom')
                       ->get();

        $enfantsActuels = $parent->enfants()->pluck('eleves.id')->all();

        return view('admin.parents.edit', compact('parent', 'eleves', 'enfantsActuels'));
    }

    // Enregistrer les modifications
    public function update(UpdateParentRequest $request, User $parent)
    {
        $this->assertEstParent($parent);

        $validated = $request->validated();

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $enfants = $validated['enfants'];
        unset($validated['enfants']);

        $parent->update($validated);
        $parent->enfants()->sync($enfants);

        return redirect()
            ->route('parents.index')
            ->with('success', "Le compte parent de {$parent->name} a été mis à jour !");
    }

    // Supprimer un compte parent
    public function destroy(User $parent)
    {
        $this->assertEstParent($parent);

        $nom = $parent->name;
        $parent->delete();

        return redirect()
            ->route('parents.index')
            ->with('success', "Le compte parent de {$nom} a été supprimé !");
    }

    // Empêche d'agir sur un compte qui n'est pas un compte Parent via un
    // identifiant d'URL modifié.
    private function assertEstParent(User $parent): void
    {
        abort_unless($parent->role === 'parent', 404);
    }
}
