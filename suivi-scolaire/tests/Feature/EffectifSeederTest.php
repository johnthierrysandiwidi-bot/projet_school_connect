<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Devoir;
use App\Models\DevoirNote;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\User;
use App\Services\MoyenneService;
use Database\Seeders\EffectifSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EffectifSeederTest extends TestCase
{
    use RefreshDatabase;

    /** Nombre de matières du programme réel, par niveau. */
    private const MATIERES_PAR_NIVEAU = [
        'CP1' => 9, 'CP2' => 9, 'CE1' => 14, 'CE2' => 14, 'CM1' => 12, 'CM2' => 12,
    ];

    public function test_seeder_creates_six_classes_with_25_students_and_a_teacher_each(): void
    {
        $this->seed(EffectifSeeder::class);

        $this->assertSame(6, Classe::count());
        $this->assertSame(150, Eleve::count());
        $this->assertSame(6, User::where('role', 'enseignant')->count());

        foreach (self::MATIERES_PAR_NIVEAU as $niveau => $nbMatieres) {
            $classe = Classe::where('niveau', $niveau)->first();

            $this->assertNotNull($classe, "La classe {$niveau} devrait exister.");
            $this->assertSame(25, $classe->eleves()->count(), "{$niveau} devrait avoir 25 élèves.");
            $this->assertSame(
                $nbMatieres,
                $classe->matieres()->count(),
                "{$niveau} devrait avoir {$nbMatieres} matières (programme réel du primaire)."
            );
            $this->assertNotNull(
                User::where('role', 'enseignant')->where('classe_id', $classe->id)->first(),
                "{$niveau} devrait avoir un enseignant assigné."
            );
        }

        $eleve = Eleve::first();
        foreach (['matricule', 'nom', 'prenom', 'date_naissance', 'sexe', 'parent_nom',
                  'parent_prenom', 'parent_telephone', 'parent_lien'] as $champ) {
            $this->assertNotEmpty($eleve->$champ, "Le champ {$champ} ne devrait pas être vide.");
        }
    }

    public function test_seeder_uses_the_real_primary_school_curriculum_with_correct_baremes(): void
    {
        $this->seed(EffectifSeeder::class);

        foreach (['CP1', 'CP2', 'CE1', 'CE2'] as $niveau) {
            $classe = Classe::where('niveau', $niveau)->first();
            $this->assertSame(
                0,
                $classe->matieres()->where('bareme', '!=', 10)->count(),
                "{$niveau} : toutes les matières devraient être notées sur 10."
            );
        }

        foreach (['CM1', 'CM2'] as $niveau) {
            $classe = Classe::where('niveau', $niveau)->first();

            $this->assertSame(5, $classe->matieres()->where('bareme', 20)->count(), "{$niveau} : 5 matières sur 20 attendues.");
            $this->assertSame(7, $classe->matieres()->where('bareme', 10)->count(), "{$niveau} : 7 matières sur 10 attendues.");

            foreach (['Étude de texte', 'Problème', 'Opération', 'Sciences', 'Histoire et géographie'] as $nom) {
                $this->assertSame(
                    20,
                    Matiere::where('classe_id', $classe->id)->where('nom', $nom)->value('bareme'),
                    "{$niveau} : « {$nom} » devrait être noté sur 20."
                );
            }
        }

        $this->assertSame(70, Matiere::count());
    }

    public function test_seeder_generates_consistent_grades_for_every_student(): void
    {
        $this->seed(EffectifSeeder::class);

        $this->assertSame(5250, Note::count());

        $classe = Classe::where('niveau', 'CP1')->first();
        $eleve = $classe->eleves()->first();

        foreach ([1, 2, 3] as $trimestre) {
            $this->assertSame(
                9,
                Note::where('eleve_id', $eleve->id)->where('trimestre', $trimestre)->count()
            );
        }

        $horsBareme = Note::query()
            ->join('matieres', 'matieres.id', '=', 'notes.matiere_id')
            ->whereColumn('notes.valeur', '>', 'matieres.bareme')
            ->orWhere('notes.valeur', '<', 0)
            ->count();
        $this->assertSame(0, $horsBareme);

        $moyenne = MoyenneService::moyenneEleve($eleve, 1, config('app.annee_scolaire'));
        $this->assertNotNull($moyenne);
        $this->assertGreaterThanOrEqual(0, $moyenne);
        $this->assertLessThanOrEqual(10, $moyenne);
    }

    public function test_seeder_does_not_duplicate_grades_on_rerun(): void
    {
        $this->seed(EffectifSeeder::class);
        $this->seed(EffectifSeeder::class);

        $this->assertSame(5250, Note::count());
    }

    public function test_seeder_does_not_overwrite_a_manually_entered_grade(): void
    {
        $classe = Classe::create([
            'niveau' => 'CP1', 'nom' => 'CP1', 'frais_scolarite' => 45000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);
        $matiere = Matiere::create([
            'nom' => 'Français', 'coefficient' => 3, 'bareme' => 10,
            'classe_id' => $classe->id, 'is_active' => true,
        ]);
        $enseignant = User::create([
            'name' => 'Prof', 'email' => 'prof.cp1.manuel@test.bf', 'password' => bcrypt('secret'),
            'role' => 'enseignant', 'classe_id' => $classe->id, 'is_active' => true,
        ]);
        $eleve = $classe->eleves()->create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => 'Dupont', 'prenom' => 'Existant', 'date_naissance' => '2019-01-01',
            'sexe' => 'M', 'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Dupont', 'parent_prenom' => 'Jean', 'parent_telephone' => '70000000',
            'parent_lien' => 'père',
        ]);

        Note::create([
            'eleve_id' => $eleve->id, 'matiere_id' => $matiere->id, 'trimestre' => 1,
            'annee_scolaire' => config('app.annee_scolaire'), 'valeur' => 8.5, 'user_id' => $enseignant->id,
        ]);

        $this->seed(EffectifSeeder::class);

        $this->assertDatabaseHas('notes', [
            'eleve_id' => $eleve->id, 'matiere_id' => $matiere->id, 'trimestre' => 1, 'valeur' => 8.5,
        ]);
    }

    public function test_seeder_fills_the_cahier_de_notes_with_devoirs(): void
    {
        $this->seed(EffectifSeeder::class);

        $this->assertSame(210, Devoir::count());

        $classe = Classe::where('niveau', 'CP1')->first();
        $devoirsNotes = Devoir::where('classe_id', $classe->id)->where('noter', true)->get();

        $this->assertGreaterThan(0, $devoirsNotes->count());

        foreach ($devoirsNotes as $devoir) {
            $this->assertSame(
                25,
                $devoir->devoirNotes()->count(),
                "Le devoir « {$devoir->titre} » devrait avoir une note pour chacun des 25 élèves."
            );
        }

        $horsBareme = DevoirNote::query()
            ->join('devoirs', 'devoirs.id', '=', 'devoir_notes.devoir_id')
            ->join('matieres', 'matieres.id', '=', 'devoirs.matiere_id')
            ->whereColumn('devoir_notes.valeur', '>', 'matieres.bareme')
            ->orWhere('devoir_notes.valeur', '<', 0)
            ->count();
        $this->assertSame(0, $horsBareme);
    }

    public function test_seeder_does_not_duplicate_devoirs_on_rerun(): void
    {
        $this->seed(EffectifSeeder::class);
        $totalApresPremierPassage = Devoir::count();
        $notesApresPremierPassage = DevoirNote::count();

        $this->seed(EffectifSeeder::class);

        $this->assertSame($totalApresPremierPassage, Devoir::count());
        $this->assertSame($notesApresPremierPassage, DevoirNote::count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(EffectifSeeder::class);
        $this->seed(EffectifSeeder::class);

        $this->assertSame(6, Classe::count());
        $this->assertSame(150, Eleve::count());
        $this->assertSame(6, User::where('role', 'enseignant')->count());
        $this->assertSame(70, Matiere::count());
    }

    public function test_seeder_tops_up_existing_class_instead_of_duplicating(): void
    {
        $classe = Classe::create([
            'niveau' => 'CP1', 'nom' => 'CP1', 'frais_scolarite' => 45000,
            'capacite_max' => 30, 'annee_scolaire' => config('app.annee_scolaire'),
        ]);

        $classe->eleves()->create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => 'Dupont', 'prenom' => 'Existant', 'date_naissance' => '2019-01-01',
            'sexe' => 'M', 'annee_scolaire' => config('app.annee_scolaire'), 'statut' => 'actif',
            'parent_nom' => 'Dupont', 'parent_prenom' => 'Jean', 'parent_telephone' => '70000000',
            'parent_lien' => 'père',
        ]);

        $this->seed(EffectifSeeder::class);

        $this->assertSame(25, $classe->eleves()->count());
        $this->assertTrue($classe->eleves()->where('nom', 'Dupont')->exists());
    }
}
