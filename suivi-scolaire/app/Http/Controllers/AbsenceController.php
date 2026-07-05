<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAbsenceRequest;
use App\Models\Absence;
use App\Models\Classe;
use App\Models\Eleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsenceController extends Controller
{
    // Feuille de présence d'une classe pour une date donnée.
    public function index(Request $request)
    {
        $annee = config('app.annee_scolaire');
        $date = $request->date ?? now()->format('Y-m-d');
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $classeId = $user->isEnseignant() ? $user->classe_id : $request->classe_id;

        // Un enseignant ne doit voir que SA classe dans le sélecteur, même si
        // sa sélection est de toute façon forcée côté serveur ci-dessus.
        $classes = $user->isEnseignant()
            ? Classe::where('id', $user->classe_id)->get()
            : Classe::orderBy('niveau')->orderBy('nom')->get();
        $classe = $classeId ? Classe::find($classeId) : $classes->first();

        $eleves = collect();
        $absences = collect();

        if ($classe) {
            $eleves = Eleve::where('classe_id', $classe->id)
                           ->where('annee_scolaire', $annee)
                           ->where('statut', 'actif')
                           ->orderBy('nom')
                           ->get();

            $absences = Absence::where('date_absence', $date)
                               ->whereIn('eleve_id', $eleves->pluck('id'))
                               ->get()
                               ->keyBy('eleve_id');
        }

        return view('admin.absences.index', compact(
            'classes', 'classe', 'eleves', 'absences', 'date', 'annee'
        ));
    }

    // Liste des absences récentes d'une classe (vue lecture, avec filtre).
    public function historique(Request $request)
    {
        $annee = config('app.annee_scolaire');
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $classeId = $user->isEnseignant() ? $user->classe_id : $request->classe_id;

        // Un enseignant ne doit voir que SA classe dans le sélecteur, même si
        // sa sélection est de toute façon forcée côté serveur ci-dessus.
        $classes = $user->isEnseignant()
            ? Classe::where('id', $user->classe_id)->get()
            : Classe::orderBy('niveau')->orderBy('nom')->get();
        $classe = $classeId ? Classe::find($classeId) : $classes->first();

        $absences = collect();
        if ($classe) {
            $absences = Absence::whereHas('eleve', function ($q) use ($classe, $annee) {
                    $q->where('classe_id', $classe->id)->where('annee_scolaire', $annee);
                })
                ->with('eleve')
                ->orderByDesc('date_absence')
                ->paginate(20);
        }

        return view('admin.absences.historique', compact('classes', 'classe', 'absences', 'annee'));
    }

    // Enregistrer la feuille de présence d'une date pour une classe.
    public function store(StoreAbsenceRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $classe = Classe::findOrFail($request->classe_id);

        // Un enseignant ne peut saisir les absences que de SA classe.
        abort_unless(
            $user->isGestionnaire() || $user->classe_id === $classe->id,
            403,
            "Vous ne pouvez gérer les absences que de votre propre classe."
        );

        $elevesAutorises = Eleve::where('classe_id', $classe->id)->pluck('id')->all();
        $date = $request->date_absence;

        // On repart de zéro pour cette date : les cases décochées
        // correspondent à des élèves désormais présents.
        Absence::where('date_absence', $date)
               ->whereIn('eleve_id', $elevesAutorises)
               ->delete();

        foreach ($request->input('absences', []) as $eleveId => $infos) {
            if (! in_array((int) $eleveId, $elevesAutorises, true)) {
                continue;
            }

            Absence::create([
                'eleve_id'     => $eleveId,
                'date_absence' => $date,
                'justifiee'    => ! empty($infos['justifiee']),
                'motif'        => $infos['motif'] ?? null,
                'user_id'      => Auth::id(),
            ]);
        }

        return redirect()
            ->route('absences.index', ['classe_id' => $classe->id, 'date' => $date])
            ->with('success', "Présences enregistrées pour le " . \Carbon\Carbon::parse($date)->format('d/m/Y') . ".");
    }
}
