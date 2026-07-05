<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\VerifiesParentAccess;
use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\Note;
use App\Services\MoyenneService;

class NoteController extends Controller
{
    use VerifiesParentAccess;

    // Notes de l'élève, regroupées par trimestre, avec la moyenne de chacun.
    public function index(Eleve $eleve)
    {
        $this->assertEnfantAutorise($eleve);

        $annee = config('app.annee_scolaire');

        $matieres = Matiere::where('classe_id', $eleve->classe_id)
                           ->where('is_active', true)
                           ->orderBy('nom')
                           ->get();

        $trimestres = collect([1, 2, 3])->map(function ($trimestre) use ($eleve, $annee, $matieres) {
            $notes = Note::where('eleve_id', $eleve->id)
                         ->where('trimestre', $trimestre)
                         ->where('annee_scolaire', $annee)
                         ->get()
                         ->keyBy('matiere_id');

            return [
                'trimestre' => $trimestre,
                'moyenne'   => MoyenneService::moyenneEleve($eleve, $trimestre, $annee),
                'matieres'  => $matieres->map(function ($matiere) use ($notes) {
                    $note = $notes->get($matiere->id);

                    return [
                        'matiere'     => $matiere->nom,
                        'coefficient' => (float) $matiere->coefficient,
                        'bareme'      => $matiere->bareme,
                        'valeur'      => $note ? (float) $note->valeur : null,
                    ];
                }),
            ];
        });

        return response()->json([
            'eleve'          => $eleve->nom_complet,
            'annee_scolaire' => $annee,
            'trimestres'     => $trimestres,
        ]);
    }
}
