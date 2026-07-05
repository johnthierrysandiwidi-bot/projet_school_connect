<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\Parametre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassageAnneeTest extends TestCase
{
    use RefreshDatabase;

    private User $gestionnaire;
    private User $enseignant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gestionnaire = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('secret'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->enseignant = User::create([
            'name' => 'Prof', 'email' => 'prof@test.bf', 'password' => bcrypt('secret'),
            'role' => 'enseignant', 'is_active' => true,
        ]);
    }

    public function test_gestionnaire_can_view_the_passage_annee_page(): void
    {
        $this->actingAs($this->gestionnaire)
            ->get('/passage-annee')
            ->assertOk();
    }

    public function test_enseignant_cannot_access_passage_annee(): void
    {
        $this->actingAs($this->enseignant)
            ->get('/passage-annee')
            ->assertForbidden();
    }

    public function test_promoting_a_student_creates_a_new_enrollment_in_the_next_class_and_year(): void
    {
        $annee = config('app.annee_scolaire');
        $classeCp1 = $this->creerClasse('CP1', $annee, 45000);
        $eleve = $this->creerEleve($classeCp1, $annee);

        $this->actingAs($this->gestionnaire)->post('/passage-annee', [
            'classe_id' => $classeCp1->id,
            'decisions' => [$eleve->id => 'promouvoir'],
        ])->assertRedirect();

        $eleve->refresh();
        $this->assertNotNull($eleve->eleveSuivant);

        $nouveauDossier = $eleve->eleveSuivant;
        $this->assertSame('actif', $nouveauDossier->statut);
        $this->assertSame($eleve->id, $nouveauDossier->eleve_origine_id);
        $this->assertSame($eleve->nom, $nouveauDossier->nom);
        $this->assertNotSame($eleve->matricule, $nouveauDossier->matricule);

        $nouvelleClasse = $nouveauDossier->classe;
        $this->assertSame('CP2', $nouvelleClasse->niveau);
        $this->assertSame(\App\Services\AnneeScolaireHelper::suivante($annee), $nouvelleClasse->annee_scolaire);
    }

    public function test_new_class_inherits_fees_and_subjects_from_the_current_year_reference_class(): void
    {
        $annee = config('app.annee_scolaire');
        $anneeSuivante = \App\Services\AnneeScolaireHelper::suivante($annee);

        $classeCp1 = $this->creerClasse('CP1', $annee, 45000);
        $eleve = $this->creerEleve($classeCp1, $annee);

        // La classe CP2 de référence pour l'année en cours a déjà ses
        // matières et ses frais propres.
        $classeCp2Reference = $this->creerClasse('CP2', $annee, 50000);
        Matiere::create(['nom' => 'Français', 'coefficient' => 3, 'bareme' => 10, 'classe_id' => $classeCp2Reference->id, 'is_active' => true]);
        Matiere::create(['nom' => 'Mathématiques', 'coefficient' => 3, 'bareme' => 10, 'classe_id' => $classeCp2Reference->id, 'is_active' => true]);

        $this->actingAs($this->gestionnaire)->post('/passage-annee', [
            'classe_id' => $classeCp1->id,
            'decisions' => [$eleve->id => 'promouvoir'],
        ]);

        $nouvelleClasseCp2 = Classe::where('niveau', 'CP2')->where('annee_scolaire', $anneeSuivante)->first();

        $this->assertNotNull($nouvelleClasseCp2);
        $this->assertEquals(50000, $nouvelleClasseCp2->frais_scolarite);
        $this->assertSame(2, $nouvelleClasseCp2->matieres()->count());
        $this->assertSame(10, $nouvelleClasseCp2->matieres()->where('nom', 'Français')->value('bareme'));
    }

    public function test_promotion_requires_explicit_destination_when_multiple_sections_exist(): void
    {
        $annee = config('app.annee_scolaire');
        $anneeSuivante = \App\Services\AnneeScolaireHelper::suivante($annee);

        $classeCp1 = $this->creerClasse('CP1', $annee, 45000);
        $eleve = $this->creerEleve($classeCp1, $annee);

        // Deux classes CP2 existent déjà pour l'année prochaine (sections A et B).
        Classe::create(['niveau' => 'CP2', 'nom' => 'CP2 A', 'frais_scolarite' => 50000, 'capacite_max' => 30, 'annee_scolaire' => $anneeSuivante]);
        $cp2b = Classe::create(['niveau' => 'CP2', 'nom' => 'CP2 B', 'frais_scolarite' => 50000, 'capacite_max' => 30, 'annee_scolaire' => $anneeSuivante]);

        // Sans préciser la classe de destination : refusé (422), pas de
        // promotion fantôme dans une section choisie au hasard.
        $this->actingAs($this->gestionnaire)->post('/passage-annee', [
            'classe_id' => $classeCp1->id,
            'decisions' => [$eleve->id => 'promouvoir'],
        ])->assertStatus(422);

        $eleve->refresh();
        $this->assertNull($eleve->eleveSuivant);

        // Avec la classe de destination précisée : la promotion réussit et
        // respecte le choix exact.
        $this->actingAs($this->gestionnaire)->post('/passage-annee', [
            'classe_id' => $classeCp1->id,
            'classe_destination_promotion' => $cp2b->id,
            'decisions' => [$eleve->id => 'promouvoir'],
        ])->assertRedirect();

        $eleve->refresh();
        $this->assertSame($cp2b->id, $eleve->eleveSuivant->classe_id);
    }

    public function test_promoting_moves_the_parent_link_to_the_new_enrollment(): void
    {
        $annee = config('app.annee_scolaire');
        $classeCp1 = $this->creerClasse('CP1', $annee, 45000);
        $eleve = $this->creerEleve($classeCp1, $annee);

        $parent = User::create([
            'name' => 'Parent', 'email' => 'parent@test.bf',
            'password' => bcrypt('secret'), 'role' => 'parent', 'is_active' => true,
        ]);
        $parent->enfants()->attach($eleve->id);

        $this->actingAs($this->gestionnaire)->post('/passage-annee', [
            'classe_id' => $classeCp1->id,
            'decisions' => [$eleve->id => 'promouvoir'],
        ]);

        $eleve->refresh();
        $parent->refresh();

        $idsEnfants = $parent->enfants()->pluck('eleves.id')->all();
        $this->assertNotContains($eleve->id, $idsEnfants);
        $this->assertContains($eleve->eleveSuivant->id, $idsEnfants);
    }

    public function test_redoubler_keeps_same_niveau_and_marks_old_record_as_redoublant(): void
    {
        $annee = config('app.annee_scolaire');
        $classeCe1 = $this->creerClasse('CE1', $annee, 50000);
        $eleve = $this->creerEleve($classeCe1, $annee);

        $this->actingAs($this->gestionnaire)->post('/passage-annee', [
            'classe_id' => $classeCe1->id,
            'decisions' => [$eleve->id => 'redoubler'],
        ]);

        $eleve->refresh();
        $this->assertSame('redoublant', $eleve->statut);

        $nouveauDossier = $eleve->eleveSuivant;
        $this->assertNotNull($nouveauDossier);
        $this->assertSame('CE1', $nouveauDossier->classe->niveau);
        $this->assertSame('actif', $nouveauDossier->statut);
    }

    public function test_quitter_marks_a_non_terminal_student_as_transfere(): void
    {
        $annee = config('app.annee_scolaire');
        $classeCe2 = $this->creerClasse('CE2', $annee, 50000);
        $eleve = $this->creerEleve($classeCe2, $annee);

        $this->actingAs($this->gestionnaire)->post('/passage-annee', [
            'classe_id' => $classeCe2->id,
            'decisions' => [$eleve->id => 'quitter'],
        ]);

        $eleve->refresh();
        $this->assertSame('transfere', $eleve->statut);
        $this->assertNull($eleve->eleveSuivant);
    }

    public function test_quitter_marks_a_cm2_student_as_diplome(): void
    {
        $annee = config('app.annee_scolaire');
        $classeCm2 = $this->creerClasse('CM2', $annee, 55000);
        $eleve = $this->creerEleve($classeCm2, $annee);

        $this->actingAs($this->gestionnaire)->post('/passage-annee', [
            'classe_id' => $classeCm2->id,
            'decisions' => [$eleve->id => 'quitter'],
        ]);

        $eleve->refresh();
        $this->assertSame('diplome', $eleve->statut);
        $this->assertNull($eleve->eleveSuivant);
    }

    public function test_a_cm2_student_cannot_be_promoted_since_there_is_no_next_level(): void
    {
        $annee = config('app.annee_scolaire');
        $classeCm2 = $this->creerClasse('CM2', $annee, 55000);
        $eleve = $this->creerEleve($classeCm2, $annee);

        $this->actingAs($this->gestionnaire)->post('/passage-annee', [
            'classe_id' => $classeCm2->id,
            'decisions' => [$eleve->id => 'promouvoir'],
        ])->assertRedirect();

        $eleve->refresh();
        $this->assertSame('actif', $eleve->statut);
        $this->assertNull($eleve->eleveSuivant);
    }

    public function test_processing_twice_does_not_duplicate_the_promotion(): void
    {
        $annee = config('app.annee_scolaire');
        $classeCp1 = $this->creerClasse('CP1', $annee, 45000);
        $eleve = $this->creerEleve($classeCp1, $annee);

        $this->actingAs($this->gestionnaire)->post('/passage-annee', [
            'classe_id' => $classeCp1->id,
            'decisions' => [$eleve->id => 'promouvoir'],
        ]);

        $this->actingAs($this->gestionnaire)->post('/passage-annee', [
            'classe_id' => $classeCp1->id,
            'decisions' => [$eleve->id => 'redoubler'], // tentative différente, doit être ignorée
        ]);

        $this->assertSame(1, Eleve::where('eleve_origine_id', $eleve->id)->count());
        $this->assertSame('actif', $eleve->refresh()->statut); // pas changé en "redoublant"
    }

    public function test_cannot_process_a_class_outside_the_active_school_year(): void
    {
        $classeAnneePassee = $this->creerClasse('CP1', '2020-2021', 45000);
        $eleve = $this->creerEleve($classeAnneePassee, '2020-2021');

        $this->actingAs($this->gestionnaire)->post('/passage-annee', [
            'classe_id' => $classeAnneePassee->id,
            'decisions' => [$eleve->id => 'promouvoir'],
        ])->assertForbidden();
    }

    // --- Page Paramètres : changer l'année active sans toucher au .env ---

    public function test_gestionnaire_can_change_the_active_school_year(): void
    {
        $this->actingAs($this->gestionnaire)->put('/parametres', [
            'annee_scolaire_active' => '2026-2027',
            'nom_ecole' => 'École Primaire Yam Wekre',
        ])->assertRedirect();

        $this->assertSame('2026-2027', Parametre::lire('annee_scolaire_active'));
    }

    public function test_active_school_year_must_match_expected_format(): void
    {
        $this->actingAs($this->gestionnaire)->put('/parametres', [
            'annee_scolaire_active' => 'pas-une-annee',
            'nom_ecole' => 'École Primaire Yam Wekre',
        ])->assertSessionHasErrors('annee_scolaire_active');
    }

    public function test_can_update_the_school_name_and_letterhead_contact_info(): void
    {
        $this->actingAs($this->gestionnaire)->put('/parametres', [
            'annee_scolaire_active' => config('app.annee_scolaire'),
            'nom_ecole' => 'Complexe Scolaire Les Hirondelles',
            'adresse_ecole' => 'Bobo-Dioulasso, Burkina Faso',
            'telephone_ecole' => '+226 70 11 22 33',
        ])->assertRedirect();

        $this->assertSame('Complexe Scolaire Les Hirondelles', Parametre::lire('nom_ecole'));
        $this->assertSame('Bobo-Dioulasso, Burkina Faso', Parametre::lire('adresse_ecole'));
        $this->assertSame('+226 70 11 22 33', Parametre::lire('telephone_ecole'));
    }

    public function test_school_name_is_required_when_updating_parametres(): void
    {
        $this->actingAs($this->gestionnaire)->put('/parametres', [
            'annee_scolaire_active' => config('app.annee_scolaire'),
            'nom_ecole' => '',
        ])->assertSessionHasErrors('nom_ecole');
    }

    public function test_enseignant_cannot_change_the_active_school_year(): void
    {
        $this->actingAs($this->enseignant)
            ->get('/parametres')
            ->assertForbidden();
    }

    private function creerClasse(string $niveau, string $annee, float $frais): Classe
    {
        return Classe::create([
            'niveau' => $niveau, 'nom' => $niveau, 'frais_scolarite' => $frais,
            'capacite_max' => 30, 'annee_scolaire' => $annee,
        ]);
    }

    private function creerEleve(Classe $classe, string $annee): Eleve
    {
        return Eleve::create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => 'Sidibé', 'prenom' => 'Ramatou', 'date_naissance' => '2017-01-01',
            'sexe' => 'F', 'classe_id' => $classe->id, 'annee_scolaire' => $annee,
            'statut' => 'actif', 'parent_nom' => 'Sidibé', 'parent_prenom' => 'Issa',
            'parent_telephone' => '70000000', 'parent_lien' => 'père',
        ]);
    }
}
