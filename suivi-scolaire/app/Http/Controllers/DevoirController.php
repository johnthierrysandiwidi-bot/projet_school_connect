<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDevoirNotesRequest;
use App\Http\Requests\StoreDevoirRequest;
use App\Models\Classe;
use App\Models\Devoir;
use App\Models\DevoirNote;
use App\Models\Eleve;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DevoirController extends Controller
{
    // Cahier de notes : liste des devoirs de la classe (de l'enseignant
    // connecté, ou choisie par le gestionnaire).
    public function index(Request $request)
    {
        $annee = config('app.annee_scolaire');
        $trimestre = $request->trimestre ?? 1;
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $classeId = $user->isEnseignant() ? $user->classe_id : $request->classe_id;

        // Un enseignant ne doit voir que SA classe dans le sélecteur, même si
        // sa sélection est de toute façon forcée côté serveur ci-dessus.
        $classes = $user->isEnseignant()
            ? Classe::where('id', $user->classe_id)->get()
            : Classe::orderBy('niveau')->orderBy('nom')->get();
        $classe = $classeId ? Classe::find($classeId) : $classes->first();

        $devoirs = collect();
        $effectifClasse = 0;
        if ($classe) {
            $effectifClasse = Eleve::where('classe_id', $classe->id)
                                   ->where('annee_scolaire', $annee)
                                   ->where('statut', 'actif')
                                   ->count();

            $devoirs = Devoir::where('classe_id', $classe->id)
                             ->where('annee_scolaire', $annee)
                             ->where('trimestre', $trimestre)
                             ->with(['matiere', 'user', 'devoirNotes'])
                             ->orderByDesc('date_devoir')
                             ->get();
        }

        return view('admin.devoirs.index', compact(
            'classes', 'classe', 'devoirs', 'trimestre', 'annee', 'effectifClasse'
        ));
    }

    // Formulaire de création d'un devoir
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isEnseignant()) {
            $classes = Classe::where('id', $user->classe_id)->get();
        } else {
            $classes = Classe::orderBy('niveau')->orderBy('nom')->get();
        }

        $matieres = Matiere::where('is_active', true)
                           ->whereIn('classe_id', $classes->pluck('id'))
                           ->orderBy('nom')
                           ->get();

        return view('admin.devoirs.create', compact('classes', 'matieres'));
    }

    // Enregistrer un nouveau devoir
    public function store(StoreDevoirRequest $request)
    {
        $validated = $request->validated();
        $this->assertClasseAutorisee((int) $validated['classe_id']);

        $validated['annee_scolaire'] = config('app.annee_scolaire');
        $validated['user_id']        = Auth::id();
        $validated['noter']          = $request->boolean('noter', true);

        $devoir = Devoir::create($validated);

        return redirect()
            ->route('devoirs.index', ['trimestre' => $devoir->trimestre])
            ->with('success', "Le devoir « {$devoir->titre} » a été créé !");
    }

    // Page de saisie des notes pour un devoir donné
    public function notes(Devoir $devoir)
    {
        $this->assertClasseAutorisee($devoir->classe_id);

        if (! $devoir->noter) {
            return redirect()
                ->route('devoirs.index', ['trimestre' => $devoir->trimestre])
                ->with('error', "Ce devoir n'est pas noté, il n'y a pas de notes à saisir.");
        }

        $eleves = Eleve::where('classe_id', $devoir->classe_id)
                       ->where('annee_scolaire', $devoir->annee_scolaire)
                       ->where('statut', 'actif')
                       ->orderBy('nom')
                       ->get();

        $notes = $devoir->devoirNotes()->get()->keyBy('eleve_id');

        return view('admin.devoirs.notes', compact('devoir', 'eleves', 'notes'));
    }

    // Enregistrer les notes saisies pour un devoir
    public function storeNotes(StoreDevoirNotesRequest $request, Devoir $devoir)
    {
        $this->assertClasseAutorisee($devoir->classe_id);

        $elevesAutorises = Eleve::where('classe_id', $devoir->classe_id)
                                ->pluck('id')
                                ->all();

        foreach ($request->input('notes', []) as $eleveId => $valeur) {
            if (! in_array((int) $eleveId, $elevesAutorises, true)) {
                continue;
            }
            if ($valeur === null || $valeur === '') {
                continue;
            }

            DevoirNote::updateOrCreate(
                ['devoir_id' => $devoir->id, 'eleve_id' => $eleveId],
                [
                    'valeur'   => $valeur,
                    'remarque' => $request->input("remarques.{$eleveId}"),
                ]
            );
        }

        return redirect()
            ->route('devoirs.index', ['trimestre' => $devoir->trimestre])
            ->with('success', "Notes du devoir « {$devoir->titre} » enregistrées !");
    }

    // Supprimer un devoir
    public function destroy(Devoir $devoir)
    {
        $this->assertClasseAutorisee($devoir->classe_id);

        $titre = $devoir->titre;
        $devoir->delete();

        return redirect()
            ->route('devoirs.index', ['trimestre' => $devoir->trimestre])
            ->with('success', "Le devoir « {$titre} » a été supprimé.");
    }

    // Un enseignant ne peut créer/gérer des devoirs que pour SA classe,
    // même si l'identifiant d'une autre classe était forgé dans la requête.
    private function assertClasseAutorisee(int $classeId): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless(
            $user->isGestionnaire() || $user->classe_id === $classeId,
            403,
            "Vous ne pouvez gérer que les devoirs de votre propre classe."
        );
    }
}
