<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnonceRequest;
use App\Models\Annonce;
use App\Models\Classe;
use Illuminate\Support\Facades\Auth;

class AnnonceController extends Controller
{
    // Liste des annonces publiées
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Annonce::with(['classe', 'user'])->orderByDesc('date_publication');

        // Un enseignant ne voit que les annonces qu'il a publiées pour sa
        // classe, ou celles destinées à toute l'école.
        if ($user->isEnseignant()) {
            $query->where(function ($q) use ($user) {
                $q->where('classe_id', $user->classe_id)
                  ->orWhereNull('classe_id');
            });
        }

        $annonces = $query->paginate(15);

        return view('admin.annonces.index', compact('annonces'));
    }

    // Formulaire de création
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $classes = $user->isEnseignant()
            ? Classe::where('id', $user->classe_id)->get()
            : Classe::orderBy('niveau')->orderBy('nom')->get();

        return view('admin.annonces.create', compact('classes'));
    }

    // Publier une annonce
    public function store(StoreAnnonceRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $validated = $request->validated();

        // Un enseignant ne peut publier que pour SA classe, jamais pour
        // toute l'école, même si le champ classe_id était vidé côté client.
        if ($user->isEnseignant()) {
            abort_unless(
                $validated['classe_id'] == $user->classe_id,
                403,
                "Vous ne pouvez publier des annonces que pour votre propre classe."
            );
        }

        $validated['user_id'] = $user->id;
        $annonce = Annonce::create($validated);

        return redirect()
            ->route('annonces.index')
            ->with('success', "L'annonce « {$annonce->titre} » a été publiée !");
    }

    // Retirer une annonce
    public function destroy(Annonce $annonce)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless(
            $user->isGestionnaire() || $annonce->user_id === $user->id,
            403,
            "Vous ne pouvez retirer que vos propres annonces."
        );

        $titre = $annonce->titre;
        $annonce->delete();

        return redirect()
            ->route('annonces.index')
            ->with('success', "L'annonce « {$titre} » a été retirée.");
    }
}
