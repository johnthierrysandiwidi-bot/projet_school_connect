<?php

namespace Database\Seeders;

use App\Models\Absence;
use App\Models\Annonce;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Matiere;
use Illuminate\Database\Seeder;
use App\Models\User; // <--- TRÈS IMPORTANT : Ne pas oublier cette ligne !

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $annee = config('app.annee_scolaire');

        // Compte Gestionnaire (accès complet)
        $gestionnaire = User::create([
            'name' => 'Administrateur',
            'email' => 'admin@ecole.bf',
            'password' => bcrypt('admin123'), // Mettez votre mot de passe ici
            'role' => 'gestionnaire',
            'is_active' => true,
        ]);

        // Une classe de démonstration, avec deux matières, pour que les
        // comptes de démo ci-dessous aient quelque chose à afficher dès la
        // première connexion (sans étape de configuration manuelle).
        $classe = Classe::create([
            'niveau' => 'CP1',
            'nom' => 'CP1',
            'frais_scolarite' => 35000,
            'capacite_max' => 30,
            'annee_scolaire' => $annee,
        ]);

        $francais = Matiere::create([
            'nom' => 'Français', 'coefficient' => 2,
            'classe_id' => $classe->id, 'is_active' => true,
        ]);
        $maths = Matiere::create([
            'nom' => 'Mathématiques', 'coefficient' => 2,
            'classe_id' => $classe->id, 'is_active' => true,
        ]);


        // Un élève de démonstration, avec quelques notes.
        $eleve = Eleve::create([
            'matricule' => Eleve::genererMatricule(),
            'nom' => 'Sidibé', 'prenom' => 'Ramatou', 'date_naissance' => '2018-04-12',
            'sexe' => 'F', 'classe_id' => $classe->id, 'annee_scolaire' => $annee,
            'statut' => 'actif', 'parent_nom' => 'Sidibé', 'parent_prenom' => 'Issa',
            'parent_telephone' => '+226 70 00 00 01', 'parent_lien' => 'père',
        ]);

        \App\Models\Note::create([
            'eleve_id' => $eleve->id, 'matiere_id' => $francais->id,
            'trimestre' => 1, 'annee_scolaire' => $annee, 'valeur' => 15, 'user_id' => $gestionnaire->id,
        ]);
        \App\Models\Note::create([
            'eleve_id' => $eleve->id, 'matiere_id' => $maths->id,
            'trimestre' => 1, 'annee_scolaire' => $annee, 'valeur' => 13.5, 'user_id' => $gestionnaire->id,
        ]);

        // Compte Parent de démonstration, lié à cet élève — pour tester
        // l'application mobile sans devoir tout créer manuellement.
        $parent = User::create([
            'name' => 'Issa Sidibé',
            'email' => 'parent@ecole.bf',
            'password' => bcrypt('parent123'),
            'role' => 'parent',
            'is_active' => true,
        ]);
        $parent->enfants()->attach($eleve->id);

        // Une absence et deux annonces de démonstration.
        Absence::create([
            'eleve_id' => $eleve->id, 'date_absence' => now()->subDays(5),
            'justifiee' => true, 'motif' => 'Maladie', 'user_id' => $gestionnaire->id,
        ]);

        Annonce::create([
            'titre' => "Réunion des parents d'élèves",
            'contenu' => "Une réunion générale aura lieu le mois prochain. Présence vivement recommandée.",
            'type' => 'reunion', 'classe_id' => null,
            'date_publication' => now(), 'user_id' => $gestionnaire->id,
        ]);
        Annonce::create([
            'titre' => 'Examen de fin de trimestre — CP1',
            'contenu' => "L'examen du premier trimestre se tiendra la semaine prochaine. Merci de réviser les leçons 1 à 8.",
            'type' => 'examen', 'classe_id' => $classe->id,
            'date_publication' => now(), 'user_id' => $gestionnaire->id,
        ]);
    }
}
