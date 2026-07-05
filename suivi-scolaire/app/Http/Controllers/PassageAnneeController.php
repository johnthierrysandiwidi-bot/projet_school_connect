<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExecuterPassageAnneeRequest;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Services\AnneeScolaireHelper;
use Illuminate\Http\Request;

class PassageAnneeController extends Controller
{
    /** Ordre des niveaux du cycle primaire, du plus petit au plus grand. */
    private const ORDRE_NIVEAUX = ['CP1', 'CP2', 'CE1', 'CE2', 'CM1', 'CM2'];

    // Écran de préparation : choix d'une classe, décision par élève.
    public function index(Request $request)
    {
        $anneeActuelle = config('app.annee_scolaire');
        $anneeSuivante = AnneeScolaireHelper::suivante($anneeActuelle);

        $classes = Classe::where('annee_scolaire', $anneeActuelle)->orderBy('niveau')->orderBy('nom')->get();
        $classeId = $request->classe_id ?? optional($classes->first())->id;
        $classe = $classeId ? Classe::find($classeId) : null;

        $eleves = collect();
        if ($classe) {
            $eleves = Eleve::where('classe_id', $classe->id)
                           ->where('statut', 'actif')
                           ->orderBy('nom')
                           ->with('eleveSuivant')
                           ->get();
        }

        $niveauSuivant = $classe ? $this->niveauSuivant($classe->niveau) : null;

        // S'il existe déjà plusieurs classes pour le niveau de destination
        // l'année prochaine (plusieurs sections), on doit demander au
        // Gestionnaire de choisir laquelle recevra les élèves — sinon, avec
        // une seule classe (ou aucune, elle sera créée automatiquement),
        // rien à demander.
        $classesPromotion = $niveauSuivant
            ? Classe::where('niveau', $niveauSuivant)->where('annee_scolaire', $anneeSuivante)->orderBy('nom')->get()
            : collect();
        $classesRedoublement = $classe
            ? Classe::where('niveau', $classe->niveau)->where('annee_scolaire', $anneeSuivante)->orderBy('nom')->get()
            : collect();

        return view('admin.passage-annee.index', compact(
            'anneeActuelle', 'anneeSuivante', 'classes', 'classe', 'eleves', 'niveauSuivant',
            'classesPromotion', 'classesRedoublement'
        ));
    }

    // Exécute les décisions prises pour une classe.
    public function executer(ExecuterPassageAnneeRequest $request)
    {
        $classe = Classe::findOrFail($request->classe_id);
        $anneeActuelle = config('app.annee_scolaire');

        abort_unless(
            $classe->annee_scolaire === $anneeActuelle,
            403,
            "Cette classe n'appartient pas à l'année scolaire active."
        );

        $anneeSuivante = AnneeScolaireHelper::suivante($anneeActuelle);
        $niveauSuivant = $this->niveauSuivant($classe->niveau);

        // Choix explicite du Gestionnaire quand plusieurs classes existent
        // déjà pour le niveau de destination (sinon null : la classe sera
        // détectée ou créée automatiquement, comme avant).
        $classeIdPromotion = $request->classe_destination_promotion;
        $classeIdRedoublement = $request->classe_destination_redoublement;

        $compteurs = ['promus' => 0, 'redoublants' => 0, 'partis' => 0, 'ignores' => 0];

        foreach ($request->decisions as $eleveId => $action) {
            $eleve = Eleve::where('classe_id', $classe->id)
                          ->where('statut', 'actif')
                          ->find($eleveId);

            // Idempotence : élève introuvable, ou déjà traité précédemment.
            if (! $eleve || $eleve->eleveSuivant()->exists()) {
                $compteurs['ignores']++;
                continue;
            }

            if ($action === 'promouvoir') {
                if (! $niveauSuivant) {
                    // CM2 n'a pas de niveau suivant : ignoré par sécurité,
                    // l'interface ne propose normalement pas ce choix.
                    $compteurs['ignores']++;
                    continue;
                }
                $this->creerDossierSuivant($eleve, $niveauSuivant, $anneeSuivante, $anneeActuelle, $classeIdPromotion);
                $compteurs['promus']++;
            } elseif ($action === 'redoubler') {
                $this->creerDossierSuivant($eleve, $classe->niveau, $anneeSuivante, $anneeActuelle, $classeIdRedoublement);
                $eleve->update(['statut' => 'redoublant']);
                $compteurs['redoublants']++;
            } elseif ($action === 'quitter') {
                $eleve->update(['statut' => $niveauSuivant ? 'transfere' : 'diplome']);
                $compteurs['partis']++;
            }
        }

        $message = "{$compteurs['promus']} élève(s) promu(s), {$compteurs['redoublants']} redoublant(s), "
                 . "{$compteurs['partis']} parti(s)/diplômé(s).";

        if ($compteurs['ignores'] > 0) {
            $message .= " {$compteurs['ignores']} déjà traité(s) ont été ignorés.";
        }

        return redirect()
            ->route('passage-annee.index', ['classe_id' => $classe->id])
            ->with('success', $message);
    }

    private function niveauSuivant(string $niveau): ?string
    {
        $index = array_search($niveau, self::ORDRE_NIVEAUX, true);

        return self::ORDRE_NIVEAUX[$index + 1] ?? null;
    }

    /**
     * Trouve (ou crée) la classe de destination pour ce niveau et cette
     * nouvelle année scolaire, en reprenant les frais et les matières de la
     * classe de ce même niveau pour l'année en cours.
     *
     * Si $classeIdChoisie est fourni (cas où plusieurs classes existent déjà
     * pour ce niveau l'année suivante — le Gestionnaire a dû choisir),
     * c'est cette classe précise qui est utilisée, sans ambiguïté possible.
     */
    private function classeDestination(string $niveau, string $anneeSuivante, string $anneeActuelle, ?int $classeIdChoisie = null): Classe
    {
        if ($classeIdChoisie) {
            return Classe::where('id', $classeIdChoisie)
                         ->where('niveau', $niveau)
                         ->where('annee_scolaire', $anneeSuivante)
                         ->firstOrFail();
        }

        $candidates = Classe::where('niveau', $niveau)->where('annee_scolaire', $anneeSuivante)->get();

        abort_if(
            $candidates->count() > 1,
            422,
            "Plusieurs classes de {$niveau} existent déjà pour {$anneeSuivante} : reviens en arrière et choisis la classe de destination avant de valider."
        );

        $classeDestination = $candidates->first();

        if ($classeDestination) {
            return $classeDestination;
        }

        $classeReference = Classe::where('niveau', $niveau)
                                 ->where('annee_scolaire', $anneeActuelle)
                                 ->first();

        $classeDestination = Classe::create([
            'niveau'          => $niveau,
            'nom'             => $niveau,
            'annee_scolaire'  => $anneeSuivante,
            'frais_scolarite' => $classeReference?->frais_scolarite ?? 0,
            'capacite_max'    => $classeReference?->capacite_max ?? 30,
        ]);

        if ($classeReference) {
            foreach ($classeReference->matieres as $matiere) {
                Matiere::create([
                    'nom'         => $matiere->nom,
                    'coefficient' => $matiere->coefficient,
                    'bareme'      => $matiere->bareme,
                    'classe_id'   => $classeDestination->id,
                    'is_active'   => true,
                ]);
            }
        }

        return $classeDestination;
    }

    /**
     * Crée le dossier de l'élève pour la nouvelle année (même niveau pour
     * un redoublement, niveau supérieur pour une promotion), en reprenant
     * toutes ses informations personnelles et celles de son parent, et en
     * déplaçant le lien parent-enfant vers ce nouveau dossier (pour que
     * l'application mobile du parent affiche l'inscription en cours).
     */
    private function creerDossierSuivant(Eleve $eleve, string $niveau, string $anneeSuivante, string $anneeActuelle, ?int $classeIdChoisie = null): Eleve
    {
        $classeDestination = $this->classeDestination($niveau, $anneeSuivante, $anneeActuelle, $classeIdChoisie);

        $nouveauDossier = Eleve::create([
            'eleve_origine_id'  => $eleve->id,
            'matricule'         => Eleve::genererMatricule(),
            'nom'               => $eleve->nom,
            'prenom'            => $eleve->prenom,
            'date_naissance'    => $eleve->date_naissance,
            'lieu_naissance'    => $eleve->lieu_naissance,
            'sexe'              => $eleve->sexe,
            'nationalite'       => $eleve->nationalite,
            'photo'             => $eleve->photo,
            'classe_id'         => $classeDestination->id,
            'annee_scolaire'    => $anneeSuivante,
            'statut'            => 'actif',
            'parent_nom'        => $eleve->parent_nom,
            'parent_prenom'     => $eleve->parent_prenom,
            'parent_telephone'  => $eleve->parent_telephone,
            'parent_telephone2' => $eleve->parent_telephone2,
            'parent_adresse'    => $eleve->parent_adresse,
            'parent_lien'       => $eleve->parent_lien,
        ]);

        foreach ($eleve->parents as $parent) {
            $parent->enfants()->detach($eleve->id);
            $parent->enfants()->attach($nouveauDossier->id);
        }

        return $nouveauDossier;
    }
}
