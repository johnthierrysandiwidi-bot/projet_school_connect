<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatieresEtImpayesTest extends TestCase
{
    use RefreshDatabase;

    private User $gestionnaire;
    private User $enseignant;
    private Classe $classe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gestionnaire = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('secret'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->classe = Classe::create([
            'niveau' => 'CP1', 'nom' => 'CP1', 'frais_scolarite' => 50000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $this->enseignant = User::create([
            'name' => 'Prof', 'email' => 'prof@test.bf', 'password' => bcrypt('secret'),
            'role' => 'enseignant', 'classe_id' => $this->classe->id, 'is_active' => true,
        ]);
    }

    // --- Matières ---

    public function test_gestionnaire_can_view_the_matieres_list(): void
    {
        $this->actingAs($this->gestionnaire)->get('/matieres')->assertOk();
    }

    public function test_enseignant_cannot_access_matieres_management(): void
    {
        $this->actingAs($this->enseignant)->get('/matieres')->assertForbidden();
    }

    public function test_gestionnaire_can_create_a_matiere(): void
    {
        $this->actingAs($this->gestionnaire)->post('/matieres', [
            'nom' => 'Anglais', 'coefficient' => 2, 'bareme' => 10, 'classe_id' => $this->classe->id,
        ])->assertRedirect(route('matieres.index'));

        $this->assertDatabaseHas('matieres', ['nom' => 'Anglais', 'classe_id' => $this->classe->id]);
    }

    public function test_gestionnaire_can_update_a_matiere(): void
    {
        $matiere = Matiere::create([
            'nom' => 'Francais', 'coefficient' => 2, 'bareme' => 10, 'classe_id' => $this->classe->id, 'is_active' => true,
        ]);

        $this->actingAs($this->gestionnaire)->put("/matieres/{$matiere->id}", [
            'nom' => 'Français', 'coefficient' => 3, 'bareme' => 10, 'classe_id' => $this->classe->id,
        ])->assertRedirect(route('matieres.index'));

        $matiere->refresh();
        $this->assertSame('Français', $matiere->nom);
        $this->assertEquals(3, $matiere->coefficient);
    }

    public function test_gestionnaire_can_delete_a_matiere(): void
    {
        $matiere = Matiere::create([
            'nom' => 'Histoire', 'coefficient' => 1, 'classe_id' => $this->classe->id, 'is_active' => true,
        ]);

        $this->actingAs($this->gestionnaire)
            ->delete("/matieres/{$matiere->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('matieres', ['id' => $matiere->id]);
    }

    public function test_matiere_deletion_from_the_classe_edit_screen_still_works(): void
    {
        // Régression : la suppression de matière était auparavant gérée par
        // ClasseController, désormais consolidée dans MatiereController,
        // mais le formulaire de classes/edit.blade.php utilise la même route.
        $matiere = Matiere::create([
            'nom' => 'Géographie', 'coefficient' => 1, 'classe_id' => $this->classe->id, 'is_active' => true,
        ]);

        $this->actingAs($this->gestionnaire)
            ->get("/classes/{$this->classe->id}/edit")
            ->assertOk()
            ->assertSee('Géographie');

        $this->actingAs($this->gestionnaire)
            ->delete("/matieres/{$matiere->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('matieres', ['id' => $matiere->id]);
    }

    // --- Impayés ---

    public function test_gestionnaire_can_view_the_impayes_page(): void
    {
        $this->actingAs($this->gestionnaire)->get('/impayes')->assertOk();
    }

    public function test_enseignant_cannot_access_impayes(): void
    {
        $this->actingAs($this->enseignant)->get('/impayes')->assertForbidden();
    }

    public function test_impayes_lists_only_students_with_an_outstanding_balance(): void
    {
        $eleveSolde = $this->creerEleve('Soldé', 'Eleve');
        Paiement::create([
            'eleve_id' => $eleveSolde->id, 'montant' => 50000, 'date_paiement' => now(),
            'mode_paiement' => 'espèces', 'reference' => Paiement::genererReference(),
            'user_id' => $this->gestionnaire->id,
        ]);

        $eleveImpaye = $this->creerEleve('Impayé', 'Eleve');
        Paiement::create([
            'eleve_id' => $eleveImpaye->id, 'montant' => 10000, 'date_paiement' => now(),
            'mode_paiement' => 'espèces', 'reference' => Paiement::genererReference(),
            'user_id' => $this->gestionnaire->id,
        ]);

        $response = $this->actingAs($this->gestionnaire)->get('/impayes');

        $response->assertOk();
        $response->assertSee('Impayé');
        $response->assertDontSee('Soldé Eleve');
    }

    public function test_impayes_can_be_filtered_by_classe(): void
    {
        $autreClasse = Classe::create([
            'niveau' => 'CM2', 'nom' => 'CM2', 'frais_scolarite' => 60000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $this->creerEleve('Kaboré', 'Awa'); // CP1, impayé (aucun paiement)

        Eleve::create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => 'Ouédraogo', 'prenom' => 'Issa', 'date_naissance' => '2017-01-01',
            'sexe' => 'M', 'classe_id' => $autreClasse->id, 'annee_scolaire' => config('app.annee_scolaire'),
            'statut' => 'actif', 'parent_nom' => 'Ouédraogo', 'parent_prenom' => 'Boukary',
            'parent_telephone' => '70000000', 'parent_lien' => 'père',
        ]);

        $response = $this->actingAs($this->gestionnaire)->get('/impayes?classe_id=' . $this->classe->id);

        $response->assertOk();
        $response->assertSee('Kaboré');
        $response->assertDontSee('Ouédraogo');
    }

    private function creerEleve(string $nom, string $prenom): Eleve
    {
        return Eleve::create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => $nom, 'prenom' => $prenom, 'date_naissance' => '2017-01-01',
            'sexe' => 'F', 'classe_id' => $this->classe->id, 'annee_scolaire' => config('app.annee_scolaire'),
            'statut' => 'actif', 'parent_nom' => $nom, 'parent_prenom' => 'Issa',
            'parent_telephone' => '70000000', 'parent_lien' => 'père',
        ]);
    }
}
