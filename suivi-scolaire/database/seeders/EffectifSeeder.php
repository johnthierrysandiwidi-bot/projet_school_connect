<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\Devoir;
use App\Models\DevoirNote;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Peuple l'établissement avec un effectif complet et réaliste :
 *  - les 6 classes du primaire (CP1 à CM2) ;
 *  - les matières de chaque classe (avec coefficients) ;
 *  - un enseignant par classe ;
 *  - 25 élèves par classe (150 au total), avec toutes les informations
 *    renseignées (élève + parent) et une photo (avatar) ;
 *  - des notes par matière pour les 3 trimestres, cohérentes par élève.
 *
 * Conçu pour être rejoué sans tout dupliquer : chaque classe/matière/
 * enseignant n'est créé qu'une fois, et les élèves ne sont complétés que
 * jusqu'à atteindre 25 par classe (utile si tu en as déjà inscrit certains
 * manuellement).
 *
 * Utilisation :
 *   php artisan db:seed --class=EffectifSeeder
 */
class EffectifSeeder extends Seeder
{
    private const EFFECTIF_CIBLE = 25;

    /** Niveau => [frais de scolarité, âge nominal en début d'année scolaire] */
    private const NIVEAUX = [
        'CP1' => ['frais' => 35000, 'age' => 6],
        'CP2' => ['frais' => 35000, 'age' => 7],
        'CE1' => ['frais' => 40000, 'age' => 8],
        'CE2' => ['frais' => 40000, 'age' => 9],
        'CM1' => ['frais' => 50000, 'age' => 10],
        'CM2' => ['frais' => 50000, 'age' => 11],
    ];

    /**
     * Programme réel du primaire (Burkina Faso), par cycle. Du CP1 au CE2,
     * toutes les matières sont notées sur 10. Au CM1/CM2, certaines matières
     * (étude de texte, problème, opération, sciences, histoire-géographie)
     * sont notées sur 20, le reste sur 10 — voir Matiere::bareme.
     */
    private const MATIERES_PAR_CYCLE = [
        'CP' => [
            'Dictée'                         => ['coefficient' => 2, 'bareme' => 10],
            'Lecture'                        => ['coefficient' => 3, 'bareme' => 10],
            'Écriture'                       => ['coefficient' => 2, 'bareme' => 10],
            'Expression orale'               => ['coefficient' => 1, 'bareme' => 10],
            'Calcul'                         => ['coefficient' => 3, 'bareme' => 10],
            'Éducation civique et morale'    => ['coefficient' => 1, 'bareme' => 10],
            'Dessin'                         => ['coefficient' => 1, 'bareme' => 10],
            'Chant'                          => ['coefficient' => 1, 'bareme' => 10],
            'Éducation physique et sportive' => ['coefficient' => 1, 'bareme' => 10],
        ],
        'CE' => [
            'Dictée'                         => ['coefficient' => 2, 'bareme' => 10],
            'Lecture'                        => ['coefficient' => 2, 'bareme' => 10],
            'Écriture'                       => ['coefficient' => 1, 'bareme' => 10],
            'Grammaire'                      => ['coefficient' => 2, 'bareme' => 10],
            'Vocabulaire'                    => ['coefficient' => 1, 'bareme' => 10],
            'Problème'                       => ['coefficient' => 2, 'bareme' => 10],
            'Opération'                      => ['coefficient' => 2, 'bareme' => 10],
            'Éducation civique et morale'    => ['coefficient' => 1, 'bareme' => 10],
            "Sciences d'observation"         => ['coefficient' => 1, 'bareme' => 10],
            'Histoire'                       => ['coefficient' => 1, 'bareme' => 10],
            'Géographie'                     => ['coefficient' => 1, 'bareme' => 10],
            'Dessin'                         => ['coefficient' => 1, 'bareme' => 10],
            'Chant'                          => ['coefficient' => 1, 'bareme' => 10],
            'Éducation physique et sportive' => ['coefficient' => 1, 'bareme' => 10],
        ],
        'CM' => [
            'Dictée'                         => ['coefficient' => 2, 'bareme' => 10],
            'Lecture'                        => ['coefficient' => 2, 'bareme' => 10],
            'Rédaction'                      => ['coefficient' => 2, 'bareme' => 10],
            'Étude de texte'                 => ['coefficient' => 2, 'bareme' => 20],
            'Problème'                       => ['coefficient' => 3, 'bareme' => 20],
            'Opération'                      => ['coefficient' => 3, 'bareme' => 20],
            'Sciences'                       => ['coefficient' => 2, 'bareme' => 20],
            'Histoire et géographie'         => ['coefficient' => 2, 'bareme' => 20],
            'Éducation civique et morale'    => ['coefficient' => 1, 'bareme' => 10],
            'Dessin'                         => ['coefficient' => 1, 'bareme' => 10],
            'Chant'                          => ['coefficient' => 1, 'bareme' => 10],
            'Éducation physique et sportive' => ['coefficient' => 1, 'bareme' => 10],
        ],
    ];

    private const NOMS_FAMILLE = [
        'Ouédraogo', 'Sawadogo', 'Kaboré', 'Compaoré', 'Zongo', 'Traoré',
        'Diallo', 'Bamogo', 'Sanou', 'Kientega', 'Nikiéma', 'Yaméogo',
        'Bationo', 'Ouattara', 'Coulibaly', 'Konaté', 'Sangaré', 'Ilboudo',
        'Tapsoba', 'Kafando',
    ];

    private const PRENOMS_M = [
        'Issa', 'Boukary', 'Adama', 'Moussa', 'Hamidou', 'Seydou', 'Ousmane',
        'Ibrahim', 'Abdoulaye', 'Karim', 'Drissa', 'Yacouba', 'Inoussa',
        'Lassané', 'Idrissa', 'Souleymane', 'Brahima', 'Salif', 'Rasmané', 'Paul',
    ];

    private const PRENOMS_F = [
        'Awa', 'Fatimata', 'Aminata', 'Ramatou', 'Mariam', 'Aïcha', 'Rasmata',
        'Salimata', 'Hadiza', 'Zalia', 'Pauline', 'Nathalie', 'Adjara',
        'Bintou', 'Korotoum', 'Habibou', 'Djénéba', 'Saratou', 'Assita', 'Lucie',
    ];

    private const VILLES = [
        'Ouagadougou', 'Bobo-Dioulasso', 'Koudougou', 'Ouahigouya', 'Banfora',
        'Kaya', 'Tenkodogo', 'Fada N\'Gourma',
    ];

    /** Palette de couleurs pour les avatars générés (RGB). */
    private const COULEURS_AVATAR = [
        [26, 86, 219], [5, 150, 105], [217, 119, 6], [185, 28, 28],
        [124, 58, 237], [219, 39, 119], [13, 148, 136], [202, 138, 4],
    ];

    private const TITRES_DEVOIRS = [
        'Devoir surveillé', 'Exercices d\'application', 'Contrôle de connaissances',
        'Devoir à la maison', 'Évaluation formative', 'Interrogation écrite',
    ];

    public function run(): void
    {
        $annee = config('app.annee_scolaire');

        foreach (self::NIVEAUX as $niveau => $infos) {
            $classe = Classe::firstOrCreate(
                ['niveau' => $niveau, 'annee_scolaire' => $annee],
                ['nom' => $niveau, 'frais_scolarite' => $infos['frais'], 'capacite_max' => 30]
            );

            $this->creerMatieres($classe);
            $enseignant = $this->creerEnseignant($classe, $niveau);
            $this->completerEffectif($classe, $infos['age'], $annee, $enseignant);
            $this->completerPhotosManquantes($classe);
            $this->genererNotes($classe, $enseignant, $annee);
            $this->genererDevoirs($classe, $enseignant, $annee);

            $this->command?->info("✅ {$niveau} : matières, enseignant, élèves, photos, notes et cahier de notes en place.");
        }
    }

    private function creerMatieres(Classe $classe): void
    {
        if ($classe->matieres()->count() > 0) {
            return;
        }

        foreach (self::MATIERES_PAR_CYCLE[$this->cycleDuNiveau($classe->niveau)] as $nom => $infos) {
            Matiere::create([
                'nom'         => $nom,
                'coefficient' => $infos['coefficient'],
                'bareme'      => $infos['bareme'],
                'classe_id'   => $classe->id,
                'is_active'   => true,
            ]);
        }
    }

    /** CP1/CP2 → CP, CE1/CE2 → CE, CM1/CM2 → CM. */
    private function cycleDuNiveau(string $niveau): string
    {
        return substr($niveau, 0, 2);
    }

    private function creerEnseignant(Classe $classe, string $niveau): User
    {
        $existant = User::where('role', 'enseignant')->where('classe_id', $classe->id)->first();
        if ($existant) {
            return $existant;
        }

        $sexe = fake()->boolean() ? 'M' : 'F';
        $prenom = $sexe === 'M' ? fake()->randomElement(self::PRENOMS_M) : fake()->randomElement(self::PRENOMS_F);
        $nom = fake()->randomElement(self::NOMS_FAMILLE);
        $email = 'prof.' . strtolower($niveau) . '@ecole.bf';

        return User::create([
            'name' => "{$nom} {$prenom}",
            'email' => $email,
            'password' => Hash::make('enseignant123'),
            'role' => 'enseignant',
            'classe_id' => $classe->id,
            'is_active' => true,
        ]);
    }

    private function completerEffectif(Classe $classe, int $ageNominal, string $annee, User $enseignant): void
    {
        $actuel = Eleve::where('classe_id', $classe->id)
                       ->where('annee_scolaire', $annee)
                       ->where('statut', 'actif')
                       ->count();

        $aCreer = self::EFFECTIF_CIBLE - $actuel;
        if ($aCreer <= 0) {
            return;
        }

        for ($i = 0; $i < $aCreer; $i++) {
            $sexe = fake()->boolean() ? 'M' : 'F';
            $prenomEleve = $sexe === 'M'
                ? fake()->randomElement(self::PRENOMS_M)
                : fake()->randomElement(self::PRENOMS_F);
            $nomFamille = fake()->randomElement(self::NOMS_FAMILLE);

            // Âge nominal du niveau, avec une légère variation réaliste (±7 mois).
            $dateNaissance = now()
                ->subYears($ageNominal)
                ->addDays(fake()->numberBetween(-210, 210));

            $parentEstLePere = fake()->boolean(70);
            $prenomParent = $parentEstLePere
                ? fake()->randomElement(self::PRENOMS_M)
                : fake()->randomElement(self::PRENOMS_F);

            $eleve = Eleve::create([
                'matricule'         => Eleve::genererMatricule(),
                'nom'               => $nomFamille,
                'prenom'            => $prenomEleve,
                'date_naissance'    => $dateNaissance->format('Y-m-d'),
                'lieu_naissance'    => fake()->randomElement(self::VILLES),
                'sexe'              => $sexe,
                'nationalite'       => 'Burkinabè',
                'classe_id'         => $classe->id,
                'annee_scolaire'    => $annee,
                'statut'            => 'actif',
                'parent_nom'        => $nomFamille,
                'parent_prenom'     => $prenomParent,
                'parent_telephone'  => $this->genererTelephone(),
                'parent_telephone2' => fake()->boolean(30) ? $this->genererTelephone() : null,
                'parent_adresse'    => fake()->randomElement(self::VILLES) . ', Secteur ' . fake()->numberBetween(1, 30),
                'parent_lien'       => $parentEstLePere ? 'père' : 'mère',
            ]);

            $eleve->update(['photo' => $this->genererPhotoAvatar($eleve)]);
        }
    }

    /**
     * Donne une photo (avatar avec initiales) aux élèves qui n'en ont pas
     * encore — utile si l'effectif a été créé avant l'ajout de cette
     * fonctionnalité, sans avoir à tout recréer.
     */
    private function completerPhotosManquantes(Classe $classe): void
    {
        $eleves = Eleve::where('classe_id', $classe->id)
                       ->where(function ($q) {
                           $q->whereNull('photo')->orWhere('photo', '');
                       })
                       ->get();

        foreach ($eleves as $eleve) {
            $eleve->update(['photo' => $this->genererPhotoAvatar($eleve)]);
        }
    }

    /**
     * Génère un avatar (cercle coloré + initiales) pour un élève et le
     * stocke dans storage/app/public/photos, comme une photo uploadée
     * normalement. Retourne le chemin relatif à enregistrer dans `photo`.
     */
    private function genererPhotoAvatar(Eleve $eleve): string
    {
        $taille = 320;
        $image = imagecreatetruecolor($taille, $taille);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        [$r, $g, $b] = self::COULEURS_AVATAR[crc32($eleve->nom . $eleve->prenom) % count(self::COULEURS_AVATAR)];
        $couleurFond = imagecolorallocate($image, $r, $g, $b);
        imagefilledellipse($image, $taille / 2, $taille / 2, $taille, $taille, $couleurFond);

        $initiales = mb_strtoupper(mb_substr($eleve->prenom, 0, 1) . mb_substr($eleve->nom, 0, 1));
        $blanc = imagecolorallocate($image, 255, 255, 255);
        $police = resource_path('fonts/DejaVuSans-Bold.ttf');
        $taillePolice = 110;

        $boite = imagettfbbox($taillePolice, 0, $police, $initiales);
        $largeurTexte = $boite[2] - $boite[0];
        $hauteurTexte = $boite[1] - $boite[7];
        $x = ($taille - $largeurTexte) / 2;
        $y = ($taille + $hauteurTexte) / 2;

        imagettftext($image, $taillePolice, 0, (int) $x, (int) $y, $blanc, $police, $initiales);

        ob_start();
        imagepng($image);
        $contenu = ob_get_clean();
        imagedestroy($image);

        $chemin = 'photos/avatar-' . $eleve->matricule . '.png';
        Storage::disk('public')->put($chemin, $contenu);

        return $chemin;
    }

    /**
     * Donne à chaque élève de la classe une note par matière et par
     * trimestre (1, 2 et 3), si elle n'existe pas déjà — pour ne jamais
     * écraser une note saisie manuellement par un enseignant.
     *
     * Chaque élève a un niveau de base stable (et qui progresse légèrement
     * au fil des trimestres), pour que les notes restent cohérentes entre
     * matières plutôt que purement aléatoires — utile pour que le
     * classement et les moyennes aient un sens.
     */
    private function genererNotes(Classe $classe, User $enseignant, string $annee): void
    {
        $matieres = Matiere::where('classe_id', $classe->id)->where('is_active', true)->get();
        if ($matieres->isEmpty()) {
            return;
        }

        $eleves = Eleve::where('classe_id', $classe->id)
                       ->where('annee_scolaire', $annee)
                       ->where('statut', 'actif')
                       ->get();

        foreach ($eleves as $eleve) {
            // Niveau de base de l'élève pour cette année, en pourcentage de
            // réussite (35% à 85%) — indépendant du barème, partagé avec
            // genererDevoirs() pour que ses notes restent cohérentes entre
            // le bulletin et le cahier de notes.
            $niveauBase = $this->niveauBaseEleve($eleve);

            foreach ([1, 2, 3] as $trimestre) {
                // Légère progression (ou régression) au fil des trimestres.
                $niveauTrimestre = $niveauBase + fake()->numberBetween(-5, 8) * ($trimestre - 1);

                foreach ($matieres as $matiere) {
                    $existe = Note::where('eleve_id', $eleve->id)
                                  ->where('matiere_id', $matiere->id)
                                  ->where('trimestre', $trimestre)
                                  ->where('annee_scolaire', $annee)
                                  ->exists();

                    if ($existe) {
                        continue;
                    }

                    $valeur = $this->genererNoteSelonBareme($niveauTrimestre, $matiere->bareme);

                    Note::create([
                        'eleve_id'       => $eleve->id,
                        'matiere_id'     => $matiere->id,
                        'valeur'         => $valeur,
                        'trimestre'      => $trimestre,
                        'annee_scolaire' => $annee,
                        'user_id'        => $enseignant->id,
                        'appreciation'   => $this->genererAppreciation($valeur, $matiere->bareme),
                    ]);
                }
            }
        }
    }

    /** Pourcentage de réussite "naturel" et stable d'un élève (35% à 85%). */
    private function niveauBaseEleve(Eleve $eleve): float
    {
        return 35 + (crc32('niveau-' . $eleve->id) % 501) / 10;
    }

    /**
     * Convertit un pourcentage de réussite (avec une variation propre à la
     * matière) en une note réelle, arrondie au demi-point, sur le barème de
     * cette matière (10 ou 20).
     */
    private function genererNoteSelonBareme(float $pourcentageBase, int $bareme): float
    {
        $pourcentage = $pourcentageBase + fake()->numberBetween(-12, 12);
        $pourcentage = max(0, min(100, $pourcentage));

        $valeur = ($pourcentage / 100) * $bareme;

        return max(0, min($bareme, round($valeur * 2) / 2));
    }

    private function genererAppreciation(float $valeur, int $bareme): string
    {
        $pourcentage = ($valeur / $bareme) * 100;

        return match (true) {
            $pourcentage >= 80 => 'Excellent travail, continuez ainsi !',
            $pourcentage >= 70 => 'Très bon trimestre.',
            $pourcentage >= 60 => 'Bon travail, des efforts à poursuivre.',
            $pourcentage >= 50 => 'Résultats moyens, peut mieux faire.',
            default             => 'Des difficultés à combler, soutien recommandé.',
        };
    }

    /**
     * Donne à chaque enseignant un cahier de notes rempli : un devoir par
     * matière et par trimestre (noté dans la grande majorité des cas, avec
     * la note de chaque élève), pour que la fonctionnalité « Cahier de
     * notes / Devoirs » ne soit pas vide à la première connexion.
     */
    private function genererDevoirs(Classe $classe, User $enseignant, string $annee): void
    {
        $matieres = Matiere::where('classe_id', $classe->id)->where('is_active', true)->get();
        $eleves = Eleve::where('classe_id', $classe->id)
                       ->where('annee_scolaire', $annee)
                       ->where('statut', 'actif')
                       ->get();

        if ($matieres->isEmpty() || $eleves->isEmpty()) {
            return;
        }

        // Période approximative de chaque trimestre dans l'année scolaire.
        $debutAnnee = \Illuminate\Support\Carbon::createFromFormat('Y', substr($annee, 0, 4))->setMonth(10)->setDay(1);
        $periodes = [
            1 => [$debutAnnee->copy(), $debutAnnee->copy()->addMonths(2)],
            2 => [$debutAnnee->copy()->addMonths(3), $debutAnnee->copy()->addMonths(5)],
            3 => [$debutAnnee->copy()->addMonths(6), $debutAnnee->copy()->addMonths(8)],
        ];

        foreach ($matieres as $matiere) {
            foreach ([1, 2, 3] as $trimestre) {
                $existe = Devoir::where('classe_id', $classe->id)
                                ->where('matiere_id', $matiere->id)
                                ->where('trimestre', $trimestre)
                                ->where('annee_scolaire', $annee)
                                ->exists();

                if ($existe) {
                    continue;
                }

                $titre = fake()->randomElement(self::TITRES_DEVOIRS) . ' — ' . $matiere->nom;
                [$debut, $fin] = $periodes[$trimestre];
                $noter = fake()->boolean(80);

                $devoir = Devoir::create([
                    'classe_id'      => $classe->id,
                    'matiere_id'     => $matiere->id,
                    'user_id'        => $enseignant->id,
                    'titre'          => $titre,
                    'description'    => 'Généré automatiquement pour peupler le cahier de notes de démonstration.',
                    'date_devoir'    => fake()->dateTimeBetween($debut, $fin)->format('Y-m-d'),
                    'trimestre'      => $trimestre,
                    'annee_scolaire' => $annee,
                    'noter'          => $noter,
                ]);

                if (! $noter) {
                    continue;
                }

                foreach ($eleves as $eleve) {
                    $valeur = $this->genererNoteSelonBareme($this->niveauBaseEleve($eleve), $matiere->bareme);

                    DevoirNote::create([
                        'devoir_id' => $devoir->id,
                        'eleve_id'  => $eleve->id,
                        'valeur'    => $valeur,
                        'remarque'  => fake()->boolean(20) ? $this->genererAppreciation($valeur, $matiere->bareme) : null,
                    ]);
                }
            }
        }
    }

    private function genererTelephone(): string
    {
        return '+226 ' . fake()->numberBetween(50, 79) . ' ' .
               fake()->numberBetween(10, 99) . ' ' .
               fake()->numberBetween(10, 99) . ' ' .
               fake()->numberBetween(10, 99);
    }
}
