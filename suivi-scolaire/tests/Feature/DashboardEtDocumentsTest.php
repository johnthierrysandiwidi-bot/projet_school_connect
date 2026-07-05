<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie le tableau de bord global, le reçu de paiement (écran + PDF)
 * et le bulletin de notes (PDF), avec des données réalistes.
 */
class DashboardEtDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_collected_payments(): void
    {
        $gestionnaire = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('secret'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $classe = Classe::create([
            'niveau' => 'CP1', 'nom' => 'CP1', 'frais_scolarite' => 50000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $eleve = Eleve::create([
            'matricule' => 'EL-2026-0001', 'nom' => 'Sidibé', 'prenom' => 'Ramatou',
            'date_naissance' => '2017-01-01', 'sexe' => 'F', 'classe_id' => $classe->id,
            'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Sidibé', 'parent_prenom' => 'Issa', 'parent_telephone' => '70000000',
            'parent_lien' => 'père',
        ]);

        Paiement::create([
            'eleve_id' => $eleve->id, 'montant' => 10000, 'date_paiement' => now(),
            'mode_paiement' => 'espèces', 'reference' => Paiement::genererReference(),
            'user_id' => $gestionnaire->id,
        ]);

        $this->actingAs($gestionnaire)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('10 000 FCFA')
            ->assertSee('CP1');
    }

    public function test_receipt_screen_and_pdf_download_work(): void
    {
        [$gestionnaire, $eleve, $paiement] = $this->scenarioAvecPaiement();

        $this->actingAs($gestionnaire);

        // La fiche "show" pointe vers le reçu (pas de vue dupliquée).
        $this->get("/paiements/{$paiement->id}")
            ->assertRedirect("/paiements/{$paiement->id}/recu");

        $this->get("/paiements/{$paiement->id}/recu")
            ->assertOk()
            ->assertSee($paiement->reference)
            ->assertSee('Sidibé');

        $response = $this->get("/paiements/{$paiement->id}/recu-pdf");
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_bulletin_pdf_can_be_generated_for_a_student_with_grades(): void
    {
        [$gestionnaire, $eleve] = $this->scenarioAvecPaiement();

        $matiere = Matiere::create([
            'nom' => 'Mathématiques', 'coefficient' => 2,
            'classe_id' => $eleve->classe_id, 'is_active' => true,
        ]);

        Note::create([
            'eleve_id' => $eleve->id, 'matiere_id' => $matiere->id, 'trimestre' => 1,
            'annee_scolaire' => config('app.annee_scolaire'), 'valeur' => 15, 'user_id' => $gestionnaire->id,
        ]);

        $response = $this->actingAs($gestionnaire)
            ->get("/notes/bulletin/{$eleve->id}");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_teacher_cannot_view_bulletin_of_a_student_outside_their_class(): void
    {
        $autreClasse = Classe::create([
            'niveau' => 'CM2', 'nom' => 'CM2', 'frais_scolarite' => 60000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $enseignant = User::create([
            'name' => 'Prof CP1', 'email' => 'prof.cp1@test.bf', 'password' => bcrypt('secret'),
            'role' => 'enseignant', 'is_active' => true, 'classe_id' => $autreClasse->id,
        ]);

        [, $eleveAutreClasse] = $this->scenarioAvecPaiement();

        $this->actingAs($enseignant)
            ->get("/notes/bulletin/{$eleveAutreClasse->id}")
            ->assertForbidden();
    }

    public function test_dashboard_shows_pedagogical_overview_with_matiere_and_moyenne(): void
    {
        $gestionnaire = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('secret'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $classe = Classe::create([
            'niveau' => 'CP1', 'nom' => 'CP1', 'frais_scolarite' => 50000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $matiere = Matiere::create([
            'nom' => 'Mathématiques', 'coefficient' => 2,
            'classe_id' => $classe->id, 'is_active' => true,
        ]);

        $eleve = Eleve::create([
            'matricule' => 'EL-2026-0002', 'nom' => 'Kaboré', 'prenom' => 'Awa',
            'date_naissance' => '2017-01-01', 'sexe' => 'F', 'classe_id' => $classe->id,
            'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Kaboré', 'parent_prenom' => 'Issouf', 'parent_telephone' => '70000000',
            'parent_lien' => 'père',
        ]);

        Note::create([
            'eleve_id' => $eleve->id, 'matiere_id' => $matiere->id, 'trimestre' => 1,
            'annee_scolaire' => config('app.annee_scolaire'), 'valeur' => 14, 'user_id' => $gestionnaire->id,
        ]);

        // Matière sur 20 (barème par défaut) : moyenne normalisée sur 10 = (14/20)*10 = 7.
        $this->actingAs($gestionnaire)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Aperçu pédagogique')
            ->assertSee('7/10')
            ->assertSee('CP1');
    }

    public function test_teacher_dashboard_shows_class_average(): void
    {
        $classe = Classe::create([
            'niveau' => 'CM2', 'nom' => 'CM2', 'frais_scolarite' => 60000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $matiere = Matiere::create([
            'nom' => 'Français', 'coefficient' => 3,
            'classe_id' => $classe->id, 'is_active' => true,
        ]);

        $enseignant = User::create([
            'name' => 'Prof CM2', 'email' => 'prof.cm2@test.bf', 'password' => bcrypt('secret'),
            'role' => 'enseignant', 'is_active' => true, 'classe_id' => $classe->id,
        ]);

        $eleve = Eleve::create([
            'matricule' => 'EL-2026-0003', 'nom' => 'Ouédraogo', 'prenom' => 'Karim',
            'date_naissance' => '2014-03-01', 'sexe' => 'M', 'classe_id' => $classe->id,
            'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Ouédraogo', 'parent_prenom' => 'Boukary', 'parent_telephone' => '71000000',
            'parent_lien' => 'père',
        ]);

        Note::create([
            'eleve_id' => $eleve->id, 'matiere_id' => $matiere->id, 'trimestre' => 1,
            'annee_scolaire' => config('app.annee_scolaire'), 'valeur' => 16, 'user_id' => $enseignant->id,
        ]);

        // Matière sur 20 (barème par défaut) : moyenne normalisée sur 10 = (16/20)*10 = 8.
        $this->actingAs($enseignant)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('8/10')
            ->assertSee('Matières configurées');
    }

    /** Construit un gestionnaire + une classe + un élève + un paiement. */
    private function scenarioAvecPaiement(): array
    {
        $gestionnaire = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('secret'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $classe = Classe::create([
            'niveau' => 'CP1', 'nom' => 'CP1', 'frais_scolarite' => 50000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $eleve = Eleve::create([
            'matricule' => 'EL-2026-0001', 'nom' => 'Sidibé', 'prenom' => 'Ramatou',
            'date_naissance' => '2017-01-01', 'sexe' => 'F', 'classe_id' => $classe->id,
            'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Sidibé', 'parent_prenom' => 'Issa', 'parent_telephone' => '70000000',
            'parent_lien' => 'père',
        ]);

        $paiement = Paiement::create([
            'eleve_id' => $eleve->id, 'montant' => 10000, 'date_paiement' => now(),
            'mode_paiement' => 'espèces', 'reference' => Paiement::genererReference(),
            'user_id' => $gestionnaire->id,
        ]);

        return [$gestionnaire, $eleve, $paiement];
    }
}
