<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Devoir;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre le « cahier de notes » : chaque enseignant peut composer des
 * devoirs (notés ou non) pour sa classe et y saisir les notes des élèves.
 */
class DevoirsTest extends TestCase
{
    use RefreshDatabase;

    private Classe $classe;
    private User $enseignant;
    private Matiere $matiere;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classe = Classe::create([
            'niveau' => 'CP1', 'nom' => 'CP1', 'frais_scolarite' => 50000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $this->matiere = Matiere::create([
            'nom' => 'Français', 'coefficient' => 2,
            'classe_id' => $this->classe->id, 'is_active' => true,
        ]);

        $this->enseignant = User::create([
            'name' => 'Prof CP1', 'email' => 'prof@test.bf', 'password' => bcrypt('secret'),
            'role' => 'enseignant', 'is_active' => true, 'classe_id' => $this->classe->id,
        ]);
    }

    public function test_teacher_can_view_the_cahier_de_notes_page(): void
    {
        $this->actingAs($this->enseignant)
            ->get('/devoirs')
            ->assertOk();
    }

    public function test_teacher_can_compose_a_graded_devoir(): void
    {
        $this->actingAs($this->enseignant);

        $this->post('/devoirs', [
            'classe_id' => $this->classe->id,
            'matiere_id' => $this->matiere->id,
            'titre' => 'Exercices de conjugaison',
            'date_devoir' => now()->toDateString(),
            'trimestre' => 1,
            'noter' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('devoirs', [
            'titre' => 'Exercices de conjugaison',
            'classe_id' => $this->classe->id,
            'noter' => 1,
        ]);
    }

    public function test_teacher_can_compose_an_ungraded_devoir(): void
    {
        $this->actingAs($this->enseignant);

        $this->post('/devoirs', [
            'classe_id' => $this->classe->id,
            'matiere_id' => $this->matiere->id,
            'titre' => 'Réviser la leçon 4',
            'date_devoir' => now()->toDateString(),
            'trimestre' => 1,
            'noter' => '0',
        ])->assertRedirect();

        $this->assertDatabaseHas('devoirs', [
            'titre' => 'Réviser la leçon 4',
            'noter' => 0,
        ]);
    }

    public function test_devoir_requires_a_title_and_a_valid_date(): void
    {
        $this->actingAs($this->enseignant);

        $this->post('/devoirs', [
            'classe_id' => $this->classe->id,
            'matiere_id' => $this->matiere->id,
            'titre' => '',
            'date_devoir' => 'pas-une-date',
            'trimestre' => 1,
        ])->assertSessionHasErrors(['titre', 'date_devoir']);
    }

    public function test_teacher_cannot_compose_a_devoir_for_another_class(): void
    {
        $autreClasse = Classe::create([
            'niveau' => 'CM2', 'nom' => 'CM2', 'frais_scolarite' => 60000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $this->actingAs($this->enseignant);

        $this->post('/devoirs', [
            'classe_id' => $autreClasse->id,
            'matiere_id' => $this->matiere->id,
            'titre' => 'Devoir interdit',
            'date_devoir' => now()->toDateString(),
            'trimestre' => 1,
        ])->assertForbidden();
    }

    public function test_teacher_can_enter_grades_for_a_devoir(): void
    {
        $eleve = $this->creerEleve();
        $devoir = $this->creerDevoir();

        $this->actingAs($this->enseignant)
            ->post("/devoirs/{$devoir->id}/notes", [
                'notes' => [$eleve->id => 14.5],
                'remarques' => [$eleve->id => 'Bon travail'],
            ])->assertRedirect();

        $this->assertDatabaseHas('devoir_notes', [
            'devoir_id' => $devoir->id, 'eleve_id' => $eleve->id,
            'valeur' => 14.5, 'remarque' => 'Bon travail',
        ]);
    }

    public function test_grade_above_twenty_is_rejected_for_a_devoir(): void
    {
        $eleve = $this->creerEleve();
        $devoir = $this->creerDevoir();

        $this->actingAs($this->enseignant)
            ->post("/devoirs/{$devoir->id}/notes", [
                'notes' => [$eleve->id => 25],
            ])->assertSessionHasErrors();
    }

    public function test_teacher_cannot_grade_a_devoir_outside_their_class(): void
    {
        $autreClasse = Classe::create([
            'niveau' => 'CM2', 'nom' => 'CM2', 'frais_scolarite' => 60000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);
        $autreMatiere = Matiere::create([
            'nom' => 'Mathématiques', 'coefficient' => 2,
            'classe_id' => $autreClasse->id, 'is_active' => true,
        ]);
        $devoirAutreClasse = Devoir::create([
            'classe_id' => $autreClasse->id, 'matiere_id' => $autreMatiere->id,
            'user_id' => $this->enseignant->id, 'titre' => 'Devoir CM2',
            'date_devoir' => now(), 'trimestre' => 1,
            'annee_scolaire' => config('app.annee_scolaire'), 'noter' => true,
        ]);

        $this->actingAs($this->enseignant)
            ->get("/devoirs/{$devoirAutreClasse->id}/notes")
            ->assertForbidden();
    }

    public function test_gestionnaire_can_view_and_create_devoirs_for_any_class(): void
    {
        $gestionnaire = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf', 'password' => bcrypt('secret'),
            'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->actingAs($gestionnaire)
            ->get('/devoirs?classe_id=' . $this->classe->id)
            ->assertOk();

        $this->actingAs($gestionnaire)
            ->post('/devoirs', [
                'classe_id' => $this->classe->id,
                'matiere_id' => $this->matiere->id,
                'titre' => 'Devoir créé par le gestionnaire',
                'date_devoir' => now()->toDateString(),
                'trimestre' => 1,
            ])->assertRedirect();

        $this->assertDatabaseHas('devoirs', ['titre' => 'Devoir créé par le gestionnaire']);
    }

    public function test_ungraded_devoir_has_no_notes_page(): void
    {
        $devoir = $this->creerDevoir(noter: false);

        $this->actingAs($this->enseignant)
            ->get("/devoirs/{$devoir->id}/notes")
            ->assertRedirect(route('devoirs.index', ['trimestre' => $devoir->trimestre]));
    }

    private function creerEleve(): Eleve
    {
        return Eleve::create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => 'Sidibé', 'prenom' => 'Ramatou', 'date_naissance' => '2017-01-01',
            'sexe' => 'F', 'classe_id' => $this->classe->id,
            'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Sidibé', 'parent_prenom' => 'Issa', 'parent_telephone' => '70000000',
            'parent_lien' => 'père',
        ]);
    }

    private function creerDevoir(bool $noter = true): Devoir
    {
        return Devoir::create([
            'classe_id' => $this->classe->id, 'matiere_id' => $this->matiere->id,
            'user_id' => $this->enseignant->id, 'titre' => 'Devoir de test',
            'date_devoir' => now(), 'trimestre' => 1,
            'annee_scolaire' => config('app.annee_scolaire'), 'noter' => $noter,
        ]);
    }
}
