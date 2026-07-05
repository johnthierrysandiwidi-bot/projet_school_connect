<?php

namespace Tests\Feature;

use App\Models\Absence;
use App\Models\Annonce;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre la gestion, côté Gestionnaire/Enseignant, des données consommées
 * par l'application mobile Parent : présences, annonces, comptes parents.
 */
class SuiviParentTest extends TestCase
{
    use RefreshDatabase;

    private Classe $classe;
    private User $gestionnaire;
    private User $enseignant;
    private Eleve $eleve;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classe = Classe::create([
            'niveau' => 'CP1', 'nom' => 'CP1', 'frais_scolarite' => 50000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $this->gestionnaire = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('secret'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->enseignant = User::create([
            'name' => 'Prof CP1', 'email' => 'prof@test.bf', 'password' => bcrypt('secret'),
            'role' => 'enseignant', 'is_active' => true, 'classe_id' => $this->classe->id,
        ]);

        $this->eleve = Eleve::create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => 'Sidibé', 'prenom' => 'Ramatou', 'date_naissance' => '2017-01-01',
            'sexe' => 'F', 'classe_id' => $this->classe->id,
            'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Sidibé', 'parent_prenom' => 'Issa', 'parent_telephone' => '70000000',
            'parent_lien' => 'père',
        ]);
    }

    // --- Absences ---

    public function test_teacher_can_view_and_save_attendance_sheet(): void
    {
        $this->actingAs($this->enseignant)
            ->get('/absences')
            ->assertOk();

        $this->actingAs($this->enseignant)
            ->post('/absences', [
                'classe_id' => $this->classe->id,
                'date_absence' => now()->toDateString(),
                'absences' => [
                    $this->eleve->id => ['justifiee' => '1', 'motif' => 'Maladie'],
                ],
            ])->assertRedirect();

        $this->assertDatabaseHas('absences', [
            'eleve_id' => $this->eleve->id, 'justifiee' => 1, 'motif' => 'Maladie',
        ]);
    }

    public function test_resubmitting_attendance_for_same_date_replaces_previous_entries(): void
    {
        Absence::create([
            'eleve_id' => $this->eleve->id, 'date_absence' => '2026-03-10',
            'justifiee' => false, 'motif' => null, 'user_id' => $this->enseignant->id,
        ]);

        // Élève marqué présent cette fois : aucune entrée ne doit rester pour cette date.
        $this->actingAs($this->enseignant)->post('/absences', [
            'classe_id' => $this->classe->id,
            'date_absence' => '2026-03-10',
            'absences' => [],
        ]);

        $this->assertDatabaseMissing('absences', ['eleve_id' => $this->eleve->id, 'date_absence' => '2026-03-10']);
    }

    public function test_teacher_cannot_record_absences_for_another_class(): void
    {
        $autreClasse = Classe::create([
            'niveau' => 'CM2', 'nom' => 'CM2', 'frais_scolarite' => 60000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $this->actingAs($this->enseignant)->post('/absences', [
            'classe_id' => $autreClasse->id,
            'date_absence' => now()->toDateString(),
            'absences' => [],
        ])->assertForbidden();
    }

    public function test_history_view_lists_recorded_absences(): void
    {
        Absence::create([
            'eleve_id' => $this->eleve->id, 'date_absence' => '2026-03-10',
            'justifiee' => true, 'motif' => 'Maladie', 'user_id' => $this->enseignant->id,
        ]);

        $this->actingAs($this->enseignant)
            ->get('/absences/historique')
            ->assertOk()
            ->assertSee('Maladie');
    }

    // --- Annonces ---

    public function test_gestionnaire_can_publish_a_school_wide_announcement(): void
    {
        $this->actingAs($this->gestionnaire)->post('/annonces', [
            'titre' => 'Réunion générale',
            'contenu' => 'Réunion des parents le 5 juillet à 9h.',
            'type' => 'reunion',
            'classe_id' => '',
            'date_publication' => now()->toDateString(),
        ])->assertRedirect(route('annonces.index'));

        $this->assertDatabaseHas('annonces', ['titre' => 'Réunion générale', 'classe_id' => null]);
    }

    public function test_teacher_can_only_publish_for_their_own_class(): void
    {
        $autreClasse = Classe::create([
            'niveau' => 'CM2', 'nom' => 'CM2', 'frais_scolarite' => 60000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $this->actingAs($this->enseignant)->post('/annonces', [
            'titre' => 'Annonce interdite',
            'contenu' => "Tentative de publier pour une autre classe.",
            'type' => 'info',
            'classe_id' => $autreClasse->id,
            'date_publication' => now()->toDateString(),
        ])->assertForbidden();
    }

    public function test_gestionnaire_can_remove_an_announcement(): void
    {
        $annonce = Annonce::create([
            'titre' => 'À retirer', 'contenu' => 'Contenu.', 'type' => 'info',
            'classe_id' => null, 'date_publication' => now(), 'user_id' => $this->gestionnaire->id,
        ]);

        $this->actingAs($this->gestionnaire)
            ->delete("/annonces/{$annonce->id}")
            ->assertRedirect(route('annonces.index'));

        $this->assertDatabaseMissing('annonces', ['id' => $annonce->id]);
    }

    // --- Comptes parents ---

    public function test_gestionnaire_can_create_a_parent_account_linked_to_a_child(): void
    {
        $this->actingAs($this->gestionnaire)->post('/parents', [
            'name' => 'Issa Sidibé',
            'email' => 'parent@test.bf',
            'password' => 'secret123',
            'enfants' => [$this->eleve->id],
        ])->assertRedirect(route('parents.index'));

        $parent = User::where('email', 'parent@test.bf')->firstOrFail();
        $this->assertSame('parent', $parent->role);
        $this->assertTrue($parent->enfants->contains($this->eleve->id));
    }

    public function test_parent_creation_requires_at_least_one_child(): void
    {
        $this->actingAs($this->gestionnaire)->post('/parents', [
            'name' => 'Issa Sidibé',
            'email' => 'parent2@test.bf',
            'password' => 'secret123',
            'enfants' => [],
        ])->assertSessionHasErrors('enfants');
    }

    public function test_teacher_cannot_manage_parent_accounts(): void
    {
        $this->actingAs($this->enseignant)
            ->get('/parents')
            ->assertForbidden();
    }

    public function test_gestionnaire_can_update_parent_children_links(): void
    {
        $parent = User::create([
            'name' => 'Issa Sidibé', 'email' => 'parent@test.bf',
            'password' => bcrypt('secret123'), 'role' => 'parent', 'is_active' => true,
        ]);
        $parent->enfants()->attach($this->eleve->id);

        $deuxiemeEnfant = Eleve::create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => 'Sidibé', 'prenom' => 'Karim', 'date_naissance' => '2018-01-01',
            'sexe' => 'M', 'classe_id' => $this->classe->id,
            'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Sidibé', 'parent_prenom' => 'Issa', 'parent_telephone' => '70000000',
            'parent_lien' => 'père',
        ]);

        $this->actingAs($this->gestionnaire)->put("/parents/{$parent->id}", [
            'name' => 'Issa Sidibé',
            'email' => 'parent@test.bf',
            'enfants' => [$this->eleve->id, $deuxiemeEnfant->id],
            'is_active' => '1',
        ])->assertRedirect(route('parents.index'));

        $this->assertCount(2, $parent->enfants()->get());
    }
}
