<?php

namespace Tests\Feature\Api;

use App\Models\Absence;
use App\Models\Annonce;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre l'API REST consommée par l'application mobile Parent :
 * authentification, tableau de bord, notes, paiements, absences, annonces.
 */
class ParentApiTest extends TestCase
{
    use RefreshDatabase;

    private Classe $classe;
    private Eleve $eleve;
    private User $parent;
    private Matiere $matiere;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classe = Classe::create([
            'niveau' => 'CP1', 'nom' => 'CP1', 'frais_scolarite' => 50000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $this->matiere = Matiere::create([
            'nom' => 'Mathématiques', 'coefficient' => 2,
            'classe_id' => $this->classe->id, 'is_active' => true,
        ]);

        $this->eleve = Eleve::create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => 'Sidibé', 'prenom' => 'Ramatou', 'date_naissance' => '2017-01-01',
            'sexe' => 'F', 'classe_id' => $this->classe->id,
            'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Sidibé', 'parent_prenom' => 'Issa', 'parent_telephone' => '70000000',
            'parent_lien' => 'père',
        ]);

        $this->parent = User::create([
            'name' => 'Issa Sidibé', 'email' => 'parent@test.bf',
            'password' => bcrypt('secret123'), 'role' => 'parent', 'is_active' => true,
        ]);

        $this->parent->enfants()->attach($this->eleve->id);
    }

    // --- Authentification ---

    public function test_parent_can_login_and_receives_a_token_with_children(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'parent@test.bf',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'parent', 'enfants'])
            ->assertJsonCount(1, 'enfants')
            ->assertJsonPath('enfants.0.nom', 'Sidibé');

        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->postJson('/api/login', [
            'email' => 'parent@test.bf',
            'password' => 'mauvais-mot-de-passe',
        ])->assertStatus(401);
    }

    public function test_non_parent_account_cannot_login_via_mobile_api(): void
    {
        User::create([
            'name' => 'Gestionnaire', 'email' => 'gest@test.bf',
            'password' => bcrypt('secret123'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->postJson('/api/login', [
            'email' => 'gest@test.bf',
            'password' => 'secret123',
        ])->assertStatus(403);
    }

    public function test_inactive_parent_cannot_login(): void
    {
        $this->parent->update(['is_active' => false]);

        $this->postJson('/api/login', [
            'email' => 'parent@test.bf',
            'password' => 'secret123',
        ])->assertStatus(403);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/enfants')->assertStatus(401);
    }

    public function test_parent_can_logout(): void
    {
        $token = $this->parent->createToken('test')->plainTextToken;
        $tokenId = $this->parent->tokens()->latest('id')->first()->id;

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }

    public function test_parent_can_change_password(): void
    {
        $token = $this->parent->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/password', [
                'current_password' => 'secret123',
                'password' => 'nouveaumdp',
                'password_confirmation' => 'nouveaumdp',
            ])->assertOk();

        $this->postJson('/api/login', [
            'email' => 'parent@test.bf',
            'password' => 'nouveaumdp',
        ])->assertOk();
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $token = $this->parent->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/password', [
                'current_password' => 'pas-le-bon',
                'password' => 'nouveaumdp',
                'password_confirmation' => 'nouveaumdp',
            ])->assertStatus(422);
    }

    // --- Tableau de bord / enfants ---

    public function test_parent_can_list_their_children(): void
    {
        $this->actingAsParent()
            ->getJson('/api/enfants')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.classe.niveau', 'CP1')
            ->assertJsonPath('data.0.classe.nom', 'CP1');
    }

    public function test_section_name_is_exposed_when_child_is_in_a_named_section(): void
    {
        // École avec plusieurs classes de CP1 (sections) : l'API doit
        // distinguer la section précise de l'enfant, pas seulement son
        // niveau pédagogique partagé par toutes les sections.
        $sectionB = Classe::create([
            'niveau' => 'CP1', 'nom' => 'CP1 B', 'frais_scolarite' => 50000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);
        $this->eleve->update(['classe_id' => $sectionB->id]);

        $this->actingAsParent()
            ->getJson('/api/enfants')
            ->assertOk()
            ->assertJsonPath('data.0.classe.niveau', 'CP1')
            ->assertJsonPath('data.0.classe.nom', 'CP1 B');
    }

    public function test_parent_can_view_child_dashboard_with_average_and_rank(): void
    {
        // La matière de ce test est sur 20 (barème par défaut) : la moyenne
        // normalisée sur 10 est donc (15/20)*10 = 7.5.
        Note::create([
            'eleve_id' => $this->eleve->id, 'matiere_id' => $this->matiere->id,
            'trimestre' => 1, 'annee_scolaire' => config('app.annee_scolaire'),
            'valeur' => 15, 'user_id' => $this->parent->id,
        ]);

        $this->actingAsParent()
            ->getJson("/api/enfants/{$this->eleve->id}?trimestre=1")
            ->assertOk()
            ->assertJsonPath('moyenne_generale', 7.5)
            ->assertJsonPath('rang', 1)
            ->assertJsonPath('total_eleves', 1)
            ->assertJsonCount(1, 'dernieres_notes')
            ->assertJsonPath('dernieres_notes.0.bareme', 20);
    }

    public function test_parent_cannot_view_dashboard_of_a_child_that_is_not_theirs(): void
    {
        $autreEleve = Eleve::create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => 'Kaboré', 'prenom' => 'Awa', 'date_naissance' => '2017-01-01',
            'sexe' => 'F', 'classe_id' => $this->classe->id,
            'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Kaboré', 'parent_prenom' => 'Issouf', 'parent_telephone' => '71000000',
            'parent_lien' => 'père',
        ]);

        $this->actingAsParent()
            ->getJson("/api/enfants/{$autreEleve->id}")
            ->assertStatus(403);
    }

    // --- Notes ---

    public function test_parent_can_view_notes_by_subject_and_trimester(): void
    {
        // Matière sur 20 (barème par défaut) : moyenne normalisée sur 10 = (17/20)*10 = 8.5.
        Note::create([
            'eleve_id' => $this->eleve->id, 'matiere_id' => $this->matiere->id,
            'trimestre' => 1, 'annee_scolaire' => config('app.annee_scolaire'),
            'valeur' => 17, 'user_id' => $this->parent->id,
        ]);

        $response = $this->actingAsParent()
            ->getJson("/api/enfants/{$this->eleve->id}/notes")
            ->assertOk();

        $response->assertJsonCount(3, 'trimestres'); // T1, T2, T3
        $response->assertJsonPath('trimestres.0.trimestre', 1);
        $response->assertJsonPath('trimestres.0.moyenne', 8.5);
        $response->assertJsonPath('trimestres.0.matieres.0.matiere', 'Mathématiques');
        $response->assertJsonPath('trimestres.0.matieres.0.valeur', 17);
        $response->assertJsonPath('trimestres.0.matieres.0.bareme', 20);
        $response->assertJsonPath('trimestres.1.moyenne', null);
    }

    // --- Paiements ---

    public function test_parent_can_view_payment_history_and_balance(): void
    {
        Paiement::create([
            'eleve_id' => $this->eleve->id, 'montant' => 20000, 'date_paiement' => now(),
            'mode_paiement' => 'espèces', 'reference' => Paiement::genererReference(),
            'user_id' => $this->parent->id,
        ]);

        $this->actingAsParent()
            ->getJson("/api/enfants/{$this->eleve->id}/paiements")
            ->assertOk()
            ->assertJsonPath('frais_total', 50000)
            ->assertJsonPath('montant_paye', 20000)
            ->assertJsonPath('reste_a_payer', 30000)
            ->assertJsonCount(1, 'paiements');
    }

    public function test_parent_can_download_a_receipt_pdf(): void
    {
        $paiement = Paiement::create([
            'eleve_id' => $this->eleve->id, 'montant' => 20000, 'date_paiement' => now(),
            'mode_paiement' => 'espèces', 'reference' => Paiement::genererReference(),
            'user_id' => $this->parent->id,
        ]);

        $response = $this->actingAsParent()
            ->get("/api/enfants/{$this->eleve->id}/paiements/{$paiement->id}/recu");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    // --- Absences ---

    public function test_parent_can_view_absences_with_motifs(): void
    {
        Absence::create([
            'eleve_id' => $this->eleve->id, 'date_absence' => '2026-03-10',
            'justifiee' => true, 'motif' => 'Maladie', 'user_id' => $this->parent->id,
        ]);
        Absence::create([
            'eleve_id' => $this->eleve->id, 'date_absence' => '2026-03-15',
            'justifiee' => false, 'motif' => null, 'user_id' => $this->parent->id,
        ]);

        $this->actingAsParent()
            ->getJson("/api/enfants/{$this->eleve->id}/absences")
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('absences.1.motif', 'Maladie'); // tri par date décroissante : le 10/03 vient après le 15/03
    }

    // --- Annonces ---

    public function test_parent_sees_school_wide_and_class_specific_announcements(): void
    {
        Annonce::create([
            'titre' => 'Réunion des parents', 'contenu' => 'Réunion générale le 5 juillet.',
            'type' => 'reunion', 'classe_id' => null,
            'date_publication' => now(), 'user_id' => $this->parent->id,
        ]);
        Annonce::create([
            'titre' => 'Examen CP1', 'contenu' => 'Examen de mathématiques le 10 juillet.',
            'type' => 'examen', 'classe_id' => $this->classe->id,
            'date_publication' => now(), 'user_id' => $this->parent->id,
        ]);

        $autreClasse = Classe::create([
            'niveau' => 'CM2', 'nom' => 'CM2', 'frais_scolarite' => 60000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);
        Annonce::create([
            'titre' => 'Examen CM2', 'contenu' => "Ne concerne pas l'enfant de ce parent.",
            'type' => 'examen', 'classe_id' => $autreClasse->id,
            'date_publication' => now(), 'user_id' => $this->parent->id,
        ]);

        $response = $this->actingAsParent()->getJson('/api/annonces')->assertOk();

        $response->assertJsonCount(2, 'annonces');
        $response->assertJsonPath('non_lues', 2);
        // L'annonce ciblée à la classe de l'élève expose son nom (et pas seulement le niveau).
        $response->assertJsonFragment(['titre' => 'Examen CP1', 'classe' => 'CP1']);
    }

    public function test_parent_can_mark_an_announcement_as_read(): void
    {
        $annonce = Annonce::create([
            'titre' => 'Échéance de paiement', 'contenu' => 'Le solde est attendu avant le 30.',
            'type' => 'paiement', 'classe_id' => null,
            'date_publication' => now(), 'user_id' => $this->parent->id,
        ]);

        $this->actingAsParent()
            ->postJson("/api/annonces/{$annonce->id}/lue")
            ->assertOk();

        $response = $this->actingAsParent()->getJson('/api/annonces')->assertOk();
        $response->assertJsonPath('non_lues', 0);
        $response->assertJsonPath('annonces.0.lu', true);
    }

    private function actingAsParent(): static
    {
        $token = $this->parent->createToken('test')->plainTextToken;
        return $this->withToken($token);
    }
}
