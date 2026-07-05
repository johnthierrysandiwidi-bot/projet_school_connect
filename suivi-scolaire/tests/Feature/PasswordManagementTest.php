<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ReinitialisationMotDePasse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Couvre le changement de mot de passe en libre-service (utilisateur déjà
 * connecté) et le parcours complet « mot de passe oublié » par email.
 */
class PasswordManagementTest extends TestCase
{
    use RefreshDatabase;

    // --- Changer son propre mot de passe ---

    public function test_logged_in_user_can_view_the_change_password_page(): void
    {
        $user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('ancienmdp'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/profil/mot-de-passe')
            ->assertOk();
    }

    public function test_user_can_change_their_own_password(): void
    {
        $user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('ancienmdp'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->actingAs($user)->put('/profil/mot-de-passe', [
            'current_password' => 'ancienmdp',
            'password' => 'nouveaumdp123',
            'password_confirmation' => 'nouveaumdp123',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('nouveaumdp123', $user->refresh()->password));
    }

    public function test_changing_password_fails_with_wrong_current_password(): void
    {
        $user = User::create([
            'name' => 'Prof', 'email' => 'prof@test.bf',
            'password' => bcrypt('ancienmdp'), 'role' => 'enseignant', 'is_active' => true,
        ]);

        $this->actingAs($user)->put('/profil/mot-de-passe', [
            'current_password' => 'pas-le-bon',
            'password' => 'nouveaumdp123',
            'password_confirmation' => 'nouveaumdp123',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('ancienmdp', $user->refresh()->password));
    }

    public function test_changing_password_requires_confirmation_to_match(): void
    {
        $user = User::create([
            'name' => 'Admin', 'email' => 'admin2@test.bf',
            'password' => bcrypt('ancienmdp'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->actingAs($user)->put('/profil/mot-de-passe', [
            'current_password' => 'ancienmdp',
            'password' => 'nouveaumdp123',
            'password_confirmation' => 'autrechose',
        ])->assertSessionHasErrors('password');
    }

    public function test_guest_cannot_access_change_password_page(): void
    {
        $this->get('/profil/mot-de-passe')->assertRedirect('/login');
    }

    // --- Mot de passe oublié ---

    public function test_forgot_password_page_is_accessible(): void
    {
        $this->get('/mot-de-passe-oublie')->assertOk();
    }

    public function test_requesting_a_reset_link_sends_a_notification_for_an_existing_account(): void
    {
        Notification::fake();

        $user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('secret'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->post('/mot-de-passe-oublie', ['email' => 'admin@test.bf'])
            ->assertRedirect();

        Notification::assertSentTo($user, ReinitialisationMotDePasse::class);
    }

    public function test_requesting_a_reset_link_gives_the_same_response_for_an_unknown_email(): void
    {
        Notification::fake();

        // Ne révèle jamais si le compte existe ou non : même redirection,
        // mais aucune notification n'est réellement envoyée.
        $response = $this->post('/mot-de-passe-oublie', ['email' => 'inconnu@test.bf']);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_with_a_valid_token(): void
    {
        Notification::fake();

        $user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('ancienmdp'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->post('/mot-de-passe-oublie', ['email' => 'admin@test.bf']);

        $token = null;
        Notification::assertSentTo($user, ReinitialisationMotDePasse::class, function ($notification) use (&$token) {
            $token = $notification->token;
            return true;
        });

        $this->get("/reinitialiser-mot-de-passe/{$token}?email=admin@test.bf")->assertOk();

        $this->post('/reinitialiser-mot-de-passe', [
            'token' => $token,
            'email' => 'admin@test.bf',
            'password' => 'unnouveaumotdepasse',
            'password_confirmation' => 'unnouveaumotdepasse',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('unnouveaumotdepasse', $user->refresh()->password));
    }

    public function test_reset_fails_with_an_invalid_token(): void
    {
        $user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('ancienmdp'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->post('/reinitialiser-mot-de-passe', [
            'token' => 'un-jeton-invalide',
            'email' => 'admin@test.bf',
            'password' => 'unnouveaumotdepasse',
            'password_confirmation' => 'unnouveaumotdepasse',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('ancienmdp', $user->refresh()->password));
    }

    public function test_user_can_login_with_new_password_after_reset(): void
    {
        Notification::fake();

        $user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('ancienmdp'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $this->post('/mot-de-passe-oublie', ['email' => 'admin@test.bf']);

        $token = null;
        Notification::assertSentTo($user, ReinitialisationMotDePasse::class, function ($notification) use (&$token) {
            $token = $notification->token;
            return true;
        });

        $this->post('/reinitialiser-mot-de-passe', [
            'token' => $token,
            'email' => 'admin@test.bf',
            'password' => 'unnouveaumotdepasse',
            'password_confirmation' => 'unnouveaumotdepasse',
        ]);

        $this->post('/login', [
            'email' => 'admin@test.bf',
            'password' => 'unnouveaumotdepasse',
        ])->assertRedirect('/dashboard');
    }
}
