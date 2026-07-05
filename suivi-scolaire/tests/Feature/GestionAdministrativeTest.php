<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Eleve;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre les parcours d'administration : classes, élèves, enseignants,
 * paiements et notes, en passant par les routes réelles (donc par les
 * Form Requests et les contrôleurs), pas seulement par les modèles.
 */
class GestionAdministrativeTest extends TestCase
{
    use RefreshDatabase;

    private User $gestionnaire;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gestionnaire = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('secret'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->actingAs($this->gestionnaire);
    }

    public function test_can_create_a_classe_with_matieres(): void
    {
        $this->post('/classes', [
            'niveau' => 'CP1',
            'nom' => 'CP1',
            'frais_scolarite' => 75000,
            'capacite_max' => 35,
            'matieres' => [
                ['nom' => 'Français', 'coefficient' => 2],
                ['nom' => 'Mathématiques', 'coefficient' => 2],
            ],
        ])->assertRedirect(route('classes.index'));

        $classe = Classe::where('niveau', 'CP1')->firstOrFail();
        $this->assertSame(2, $classe->matieres()->count());
    }

    public function test_classe_creation_rejects_invalid_niveau(): void
    {
        $this->post('/classes', [
            'niveau' => 'TERMINALE', 'nom' => 'Terminale', 'frais_scolarite' => 1000, 'capacite_max' => 10,
        ])->assertSessionHasErrors('niveau');
    }

    public function test_can_create_a_second_classe_for_the_same_niveau(): void
    {
        $this->creerClasse(); // crée "CP1"

        $this->post('/classes', [
            'niveau' => 'CP1',
            'nom' => 'CP1 B',
            'frais_scolarite' => 75000,
            'capacite_max' => 35,
        ])->assertRedirect(route('classes.index'));

        $this->assertSame(2, Classe::where('niveau', 'CP1')->count());
    }

    public function test_cannot_create_two_classes_with_the_same_nom_in_the_same_year(): void
    {
        $this->creerClasse(); // crée "CP1"

        $this->post('/classes', [
            'niveau' => 'CP2',
            'nom' => 'CP1', // même nom, niveau différent : doit échouer quand même
            'frais_scolarite' => 75000,
            'capacite_max' => 35,
        ])->assertSessionHasErrors('nom');

        $this->assertSame(1, Classe::count());
    }

    public function test_can_update_a_classe_and_add_a_matiere(): void
    {
        $classe = $this->creerClasse();

        $this->put("/classes/{$classe->id}", [
            'nom' => $classe->nom,
            'frais_scolarite' => 80000,
            'capacite_max' => 40,
            'nouvelles_matieres' => [
                ['nom' => 'Éveil scientifique', 'coefficient' => 1],
            ],
        ])->assertRedirect(route('classes.show', $classe));

        $classe->refresh();
        $this->assertEquals(80000, $classe->frais_scolarite);
        $this->assertSame(2, $classe->matieres()->count());
    }

    public function test_can_register_a_student(): void
    {
        $classe = $this->creerClasse();

        $this->post('/eleves', [
            'nom' => 'Kaboré', 'prenom' => 'Awa', 'date_naissance' => '2016-05-12',
            'sexe' => 'F', 'classe_id' => $classe->id,
            'parent_nom' => 'Kaboré', 'parent_prenom' => 'Issouf',
            'parent_telephone' => '76000000', 'parent_lien' => 'père',
        ])->assertRedirect(route('eleves.index'));

        $this->assertDatabaseHas('eleves', ['nom' => 'Kaboré', 'classe_id' => $classe->id]);
    }

    public function test_student_birthdate_cannot_be_in_the_future(): void
    {
        $classe = $this->creerClasse();

        $this->post('/eleves', [
            'nom' => 'X', 'prenom' => 'Y', 'date_naissance' => now()->addYear()->toDateString(),
            'sexe' => 'F', 'classe_id' => $classe->id,
            'parent_nom' => 'X', 'parent_prenom' => 'Y',
            'parent_telephone' => '7000', 'parent_lien' => 'père',
        ])->assertSessionHasErrors('date_naissance');
    }

    public function test_can_update_and_soft_delete_a_student(): void
    {
        $classe = $this->creerClasse();
        $eleve = $this->creerEleve($classe);

        $this->put("/eleves/{$eleve->id}", [
            'nom' => 'Kaboré', 'prenom' => 'Awa', 'date_naissance' => '2016-05-12',
            'sexe' => 'F', 'classe_id' => $classe->id, 'statut' => 'actif',
            'parent_nom' => 'Kaboré', 'parent_prenom' => 'Issouf',
            'parent_telephone' => '76000001', 'parent_lien' => 'père',
        ])->assertRedirect(route('eleves.show', $eleve));

        $this->delete("/eleves/{$eleve->id}")->assertRedirect(route('eleves.index'));
        $this->assertSoftDeleted('eleves', ['id' => $eleve->id]);
    }

    public function test_can_create_a_teacher_account_and_rejects_duplicate_email(): void
    {
        $classe = $this->creerClasse();

        $this->post('/enseignants', [
            'name' => 'Prof Test', 'email' => 'prof@test.bf',
            'password' => 'secret', 'classe_id' => $classe->id,
        ])->assertRedirect(route('enseignants.index'));

        $enseignant = User::where('email', 'prof@test.bf')->firstOrFail();
        $this->assertSame('enseignant', $enseignant->role);

        $this->post('/enseignants', [
            'name' => 'Doublon', 'email' => 'prof@test.bf',
            'password' => 'secret', 'classe_id' => $classe->id,
        ])->assertSessionHasErrors('email');
    }

    public function test_updating_a_teacher_without_password_keeps_the_old_one(): void
    {
        $classe = $this->creerClasse();

        $this->post('/enseignants', [
            'name' => 'Prof Test', 'email' => 'prof@test.bf',
            'password' => 'secret', 'classe_id' => $classe->id,
        ]);

        $enseignant = User::where('email', 'prof@test.bf')->firstOrFail();
        $ancienHash = $enseignant->password;

        $this->put("/enseignants/{$enseignant->id}", [
            'name' => 'Prof Test Modifié', 'email' => 'prof@test.bf',
            'classe_id' => $classe->id, 'is_active' => '1',
        ])->assertRedirect(route('enseignants.index'));

        $enseignant->refresh();
        $this->assertSame('Prof Test Modifié', $enseignant->name);
        $this->assertSame($ancienHash, $enseignant->password);
    }

    public function test_can_record_a_payment_and_rejects_invalid_amount(): void
    {
        $classe = $this->creerClasse();
        $eleve = $this->creerEleve($classe);

        $this->post('/paiements', [
            'eleve_id' => $eleve->id, 'montant' => 25000,
            'date_paiement' => now()->toDateString(), 'mode_paiement' => 'espèces',
        ])->assertRedirect();

        $this->assertSame(1, $eleve->paiements()->count());

        $this->post('/paiements', [
            'eleve_id' => $eleve->id, 'montant' => 0,
            'date_paiement' => now()->toDateString(), 'mode_paiement' => 'espèces',
        ])->assertSessionHasErrors('montant');
    }

    public function test_can_enter_grades_for_a_student(): void
    {
        $classe = $this->creerClasse();
        $eleve = $this->creerEleve($classe);
        $matiere = $classe->matieres()->first(); // bareme par défaut : 10

        $this->post('/notes', [
            'trimestre' => 1,
            'annee_scolaire' => config('app.annee_scolaire'),
            'notes' => [
                $eleve->id => [$matiere->id => 8.5],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'eleve_id' => $eleve->id, 'matiere_id' => $matiere->id, 'valeur' => 8.5,
        ]);
    }

    public function test_grade_above_the_subjects_bareme_is_rejected(): void
    {
        $classe = $this->creerClasse();
        $eleve = $this->creerEleve($classe);
        $matiere = $classe->matieres()->first();

        $this->post('/notes', [
            'trimestre' => 1,
            'annee_scolaire' => config('app.annee_scolaire'),
            'notes' => [
                $eleve->id => [$matiere->id => 25],
            ],
        ])->assertSessionHasErrors();
    }

    public function test_classe_edit_page_does_not_nest_forms(): void
    {
        $classe = $this->creerClasse(); // a déjà une matière

        $html = $this->get("/classes/{$classe->id}/edit")->getContent();

        // Un <form> ne doit jamais s'ouvrir avant que le précédent ne soit
        // refermé (régression : le bouton "Enregistrer" restait inerte
        // lorsque la classe avait déjà une matière, à cause d'un <form>
        // de suppression imbriqué dans le formulaire principal).
        $depth = 0;
        foreach (preg_split('/(<form\b|<\/form>)/i', $html, -1, PREG_SPLIT_DELIM_CAPTURE) as $token) {
            if (stripos($token, '<form') === 0) {
                $this->assertSame(0, $depth, 'Un <form> a été ouvert alors qu\'un autre était déjà ouvert.');
                $depth++;
            } elseif (stripos($token, '</form>') === 0) {
                $depth--;
            }
        }
        $this->assertSame(0, $depth, 'Un <form> est resté ouvert (balise de fermeture manquante).');
    }

    /** Crée une classe CP1 avec une matière, pour les tests qui en ont besoin. */
    private function creerClasse(): Classe
    {
        $this->post('/classes', [
            'niveau' => 'CP1',
            'nom' => 'CP1',
            'frais_scolarite' => 75000,
            'capacite_max' => 35,
            'matieres' => [['nom' => 'Mathématiques', 'coefficient' => 2]],
        ]);

        return Classe::where('niveau', 'CP1')->firstOrFail();
    }

    /** Inscrit un élève de démonstration dans la classe donnée. */
    private function creerEleve(Classe $classe): Eleve
    {
        return Eleve::create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => 'Sidibé', 'prenom' => 'Ramatou', 'date_naissance' => '2017-01-01',
            'sexe' => 'F', 'classe_id' => $classe->id,
            'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Sidibé', 'parent_prenom' => 'Issa', 'parent_telephone' => '70000000',
            'parent_lien' => 'père',
        ]);
    }
}
