<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Models\Note;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Matiere;
use App\Services\MoyenneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $annee = config('app.annee_scolaire');
        $trimestre = $request->trimestre ?? 1;
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isEnseignant()) {
            $classeId = $user->classe_id;
        } else {
            $classeId = $request->classe_id;
        }

        // Un enseignant ne doit voir que SA classe dans le sélecteur, même si
        // sa sélection est de toute façon forcée côté serveur ci-dessus.
        $classes = $user->isEnseignant()
            ? Classe::where('id', $user->classe_id)->get()
            : Classe::orderBy('niveau')->orderBy('nom')->get();
        $classe = $classeId ? Classe::find($classeId) : $classes->first();

        if (!$classe) {
            return view('admin.notes.index', [
                'classes' => $classes,
                'classe' => null,
                'eleves' => collect(),
                'matieres' => collect(),
                'notes' => collect(),
                'trimestre' => $trimestre,
                'annee' => $annee,
            ]);
        }

        $eleves = Eleve::where('classe_id', $classe->id)
                       ->where('annee_scolaire', $annee)
                       ->where('statut', 'actif')
                       ->orderBy('nom')
                       ->get();

        $matieres = Matiere::where('classe_id', $classe->id)
                           ->where('is_active', true)
                           ->orderBy('nom')
                           ->get();

        $notes = Note::where('trimestre', $trimestre)
                     ->where('annee_scolaire', $annee)
                     ->whereIn('eleve_id', $eleves->pluck('id'))
                     ->get()
                     ->groupBy('eleve_id')
                     ->map(fn($n) => $n->keyBy('matiere_id'));

        return view('admin.notes.index', compact(
            'classes', 'classe', 'eleves', 'matieres', 'notes', 'trimestre', 'annee'
        ));
    }

    public function store(StoreNoteRequest $request)
    {
        $trimestre = $request->trimestre;
        $annee = $request->annee_scolaire;
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Un enseignant ne peut saisir que les notes des élèves de SA classe,
        // même si l'identifiant d'un autre élève était forgé dans la requête.
        $elevesAutorises = null;
        if ($user->isEnseignant()) {
            $elevesAutorises = Eleve::where('classe_id', $user->classe_id)
                                    ->pluck('id')
                                    ->all();
        }

        foreach ($request->notes ?? [] as $eleveId => $matiereNotes) {
            if ($elevesAutorises !== null && ! in_array((int) $eleveId, $elevesAutorises, true)) {
                continue;
            }

            foreach ($matiereNotes as $matiereId => $valeur) {
                if ($valeur === null || $valeur === '') continue;

                Note::updateOrCreate(
                    [
                        'eleve_id'       => $eleveId,
                        'matiere_id'     => $matiereId,
                        'trimestre'      => $trimestre,
                        'annee_scolaire' => $annee,
                    ],
                    [
                        'valeur'  => $valeur,
                        'user_id' => Auth::id(),
                    ]
                );
            }
        }

        return redirect()->back()
            ->with('success', "Notes du trimestre {$trimestre} enregistrées avec succès !");
    }

    public function classement(Request $request)
    {
        $annee = config('app.annee_scolaire');
        $trimestre = $request->trimestre ?? 1;
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isEnseignant()) {
            $classeId = $user->classe_id;
        } else {
            $classeId = $request->classe_id;
        }

        // Un enseignant ne doit voir que SA classe dans le sélecteur, même si
        // sa sélection est de toute façon forcée côté serveur ci-dessus.
        $classes = $user->isEnseignant()
            ? Classe::where('id', $user->classe_id)->get()
            : Classe::orderBy('niveau')->orderBy('nom')->get();
        $classe = $classeId ? Classe::find($classeId) : $classes->first();

        if (!$classe) {
            return view('admin.notes.classement', [
                'classes' => $classes,
                'classe' => null,
                'classement' => collect(),
                'trimestre' => $trimestre,
                'annee' => $annee,
            ]);
        }

        $classement = MoyenneService::classement($classe->id, $trimestre, $annee);

        return view('admin.notes.classement', compact(
            'classes', 'classe', 'classement', 'trimestre', 'annee'
        ));
    }

    // Bulletin de notes PDF
    public function bulletin(Request $request, Eleve $eleve)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Un enseignant ne peut consulter que les bulletins de SA classe.
        if ($user->isEnseignant() && $eleve->classe_id !== $user->classe_id) {
            abort(403, "Vous ne pouvez consulter que les bulletins de votre propre classe.");
        }

        $annee = config('app.annee_scolaire');
        $trimestre = $request->trimestre ?? 1;

        $eleve->load(['classe.matieres']);
        $matieres = $eleve->classe->matieres()->where('is_active', true)->get();

        $notes = Note::where('eleve_id', $eleve->id)
                     ->where('trimestre', $trimestre)
                     ->where('annee_scolaire', $annee)
                     ->with('matiere')
                     ->get()
                     ->keyBy('matiere_id');

        $moyenne = MoyenneService::moyenneEleve($eleve, $trimestre, $annee);
        ['rang' => $rang, 'total_eleves' => $totalEleves] = MoyenneService::rangEleve($eleve, $trimestre, $annee);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.bulletins.bulletin', compact(
            'eleve', 'matieres', 'notes', 'moyenne', 'trimestre', 'rang', 'totalEleves', 'annee'
        ));

        $pdf->setPaper('A4', 'portrait');
        return $pdf->download("bulletin-{$eleve->matricule}-T{$trimestre}.pdf");
    }
}