<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Eleve;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Vérifie que les accesseurs de photo de l'élève (photo_url, utilisée par
 * le web et l'API mobile ; photo_base64, utilisée dans les PDF) se
 * comportent correctement à la fois quand le fichier existe et quand la
 * colonne `photo` pointe vers un fichier qui n'existe plus — un cas réel
 * (nettoyage du stockage, restauration partielle de la base...) qui ne
 * doit jamais afficher une image cassée, mais retomber sur les initiales.
 */
class EleveAvatarTest extends TestCase
{
    use RefreshDatabase;

    private function creerEleve(): Eleve
    {
        $classe = Classe::create([
            'niveau' => 'CP1', 'nom' => 'CP1', 'frais_scolarite' => 50000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        return Eleve::create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => 'Sidibé', 'prenom' => 'Ramatou', 'date_naissance' => '2017-01-01',
            'sexe' => 'F', 'classe_id' => $classe->id,
            'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Sidibé', 'parent_prenom' => 'Issa', 'parent_telephone' => '70000000',
            'parent_lien' => 'père',
        ]);
    }

    public function test_photo_url_is_null_when_no_photo_is_set(): void
    {
        $eleve = $this->creerEleve();

        $this->assertNull($eleve->photo_url);
        $this->assertNull($eleve->photo_base64);
    }

    public function test_photo_url_is_null_when_the_file_is_missing(): void
    {
        Storage::fake('public');

        $eleve = $this->creerEleve();
        // Le chemin est enregistré en base, mais le fichier n'a jamais été
        // créé sur ce disque (simulé ici) : c'est exactement le cas d'un
        // fichier supprimé après coup.
        $eleve->update(['photo' => 'photos/disparu.jpg']);

        $this->assertNull($eleve->photo_url);
        $this->assertNull($eleve->photo_base64);
    }

    public function test_photo_url_works_when_the_file_actually_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('photos/existe.jpg', 'contenu-image-factice');

        $eleve = $this->creerEleve();
        $eleve->update(['photo' => 'photos/existe.jpg']);

        $this->assertNotNull($eleve->photo_url);
        $this->assertStringContainsString('photos/existe.jpg', $eleve->photo_url);
        $this->assertNotNull($eleve->photo_base64);
        $this->assertStringStartsWith('data:', $eleve->photo_base64);
    }

    public function test_eleves_list_shows_initials_instead_of_a_broken_image_when_file_is_missing(): void
    {
        Storage::fake('public');

        $gestionnaire = \App\Models\User::create([
            'name' => 'Admin', 'email' => 'admin@test.bf',
            'password' => bcrypt('secret'), 'role' => 'gestionnaire', 'is_active' => true,
        ]);

        $eleve = $this->creerEleve();
        $eleve->update(['photo' => 'photos/disparu.jpg']);

        $this->actingAs($gestionnaire)
            ->get('/eleves')
            ->assertOk()
            // Les initiales de "Ramatou Sidibé" : RS.
            ->assertSee('RS');
    }
}
