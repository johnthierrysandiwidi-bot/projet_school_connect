<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie l'authentification et la séparation des rôles Gestionnaire /
 * Enseignant définie dans le cahier des charges :
 *  - un Enseignant ne doit accéder qu'aux notes/au classement de sa classe ;
 *  - tout le reste (élèves, classes, paiements, comptes enseignants) est
 *    réservé au Gestionnaire.
 */
class AuthAndRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_login_requires_valid_credentials(): void
    {
        $this->post('/login', [
            'email' => 'not-an-email',
            'password' => '',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_user_can_login_and_is_redirected_to_dashboard(): void
    {
        User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('secret'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'admin@test.bf',
            'password' => 'secret',
        ])->assertRedirect('/dashboard');
    }

    public function test_inactive_account_cannot_login(): void
    {
        User::create([
            'name' => 'Désactivé', 'email' => 'inactif@test.bf',
            'password' => bcrypt('secret'), 'role' => 'gestionnaire', 'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => 'inactif@test.bf',
            'password' => 'secret',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_gestionnaire_can_access_every_admin_page(): void
    {
        $gestionnaire = User::create([
            'name' => 'Admin', 'email' => 'admin2@test.bf',
            'password' => bcrypt('secret'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->actingAs($gestionnaire);

        $this->get('/dashboard')->assertOk();
        $this->get('/eleves')->assertOk();
        $this->get('/classes')->assertOk();
        $this->get('/enseignants')->assertOk();
        $this->get('/paiements')->assertOk();
        $this->get('/notes')->assertOk();
    }

    public function test_enseignant_is_restricted_to_pedagogy_pages(): void
    {
        $classe = Classe::create([
            'niveau' => 'CP1', 'nom' => 'CP1', 'frais_scolarite' => 50000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $enseignant = User::create([
            'name' => 'Prof Démo', 'email' => 'prof@test.bf', 'password' => bcrypt('secret'),
            'role' => 'enseignant', 'is_active' => true, 'classe_id' => $classe->id,
        ]);

        $this->actingAs($enseignant);

        // Autorisé : tableau de bord et pédagogie
        $this->get('/dashboard')->assertOk();
        $this->get('/notes')->assertOk();
        $this->get('/notes/classement')->assertOk();

        // Interdit : tout ce qui relève du Gestionnaire
        $this->get('/eleves')->assertForbidden();
        $this->get('/eleves/create')->assertForbidden();
        $this->get('/classes')->assertForbidden();
        $this->get('/enseignants')->assertForbidden();
        $this->get('/paiements')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/eleves')->assertRedirect('/login');
    }
}
