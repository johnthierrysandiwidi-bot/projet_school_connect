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
use Illuminate\Support\Facades\Storage;

/**
 * Peuple l'établissement avec exactement :
 *  - 10 élèves par classe (CP1 à CM2) = 60 élèves au total
 *  - 25 parents avec enfants :
 *      • 3 parents × 4 enfants chacun  = 12 élèves
 *      • 8 parents × 3 enfants chacun  = 24 élèves
 *      • 10 parents × 2 enfants chacun = 20 élèves
 *      • 4 élèves restants = 1 parent chacun = 4 parents supplémentaires
 *      Total : 25 comptes parents
 *  - Une photo (avatar) pour chaque élève
 *  - Des notes pour les 3 trimestres
 *
 * Utilisation :
 *   php artisan db:seed --class=EffectifEcoleSeeder
 */
class EffectifEcoleSeeder extends Seeder
{
    private const EFFECTIF_PAR_CLASSE = 10;

    private const PRENOMS_M = [
        'Issa', 'Boukary', 'Adama', 'Moussa', 'Hamidou', 'Seydou', 'Ousmane',
        'Ibrahim', 'Abdoulaye', 'Karim', 'Drissa', 'Yacouba', 'Inoussa',
        'Lassané', 'Idrissa', 'Souleymane', 'Brahima', 'Salif', 'Rasmané', 'Paul',
        'Fidèle', 'Constant', 'Désiré', 'Blaise', 'Justin',
    ];

    private const PRENOMS_F = [
        'Awa', 'Fatimata', 'Aminata', 'Ramatou', 'Mariam', 'Aïcha', 'Rasmata',
        'Salimata', 'Hadiza', 'Zalia', 'Pauline', 'Nathalie', 'Adjara',
        'Bintou', 'Korotoum', 'Habibou', 'Djénéba', 'Saratou', 'Assita', 'Lucie',
        'Cécile', 'Léonie', 'Flore', 'Joëlle', 'Virginie',
    ];

    private const NOMS_FAMILLE = [
        'Ouédraogo', 'Sawadogo', 'Kaboré', 'Compaoré', 'Zongo', 'Traoré',
        'Diallo', 'Bamogo', 'Sanou', 'Kientega', 'Nikiéma', 'Yaméogo',
        'Bationo', 'Ouattara', 'Coulibaly', 'Konaté', 'Sangaré', 'Ilboudo',
        'Tiendrebeogo', 'Savadogo', 'Tapsoba', 'Belem', 'Compaore', 'Guigma',
        'Yaro', 'Pafadnam', 'Kabré', 'Balima', 'Bouda', 'Dare',
    ];

    private const VILLES = [
        'Ouagadougou', 'Bobo-Dioulasso', 'Koudougou', 'Ouahigouya', 'Banfora',
        'Kaya', 'Tenkodogo', "Fada N'Gourma",
    ];

    private const COULEURS_AVATAR = [
        [26, 86, 219], [5, 150, 105], [217, 119, 6], [185, 28, 28],
        [124, 58, 237], [219, 39, 119], [13, 148, 136], [202, 138, 4],
        [30, 64, 175], [4, 120, 87], [180, 83, 9], [153, 27, 27],
    ];

    private const AGES_PAR_NIVEAU = [
        'CP1' => 6, 'CP2' => 7, 'CE1' => 8,
        'CE2' => 9, 'CM1' => 10, 'CM2' => 11,
    ];

    public function run(): void
    {
        $annee = config('app.annee_scolaire');
        fake()->seed(42); // graine fixe pour des données reproductibles

        $this->command->info("Création des élèves (10 par classe)...");
        $tousLesEleves = $this->creerEleves($annee);

        $this->command->info("Création des 25 comptes parents...");
        $this->creerParents($tousLesEleves);

        $this->command->info("Génération des notes (3 trimestres)...");
        $this->genererNotes($annee);

        $this->command->info(sprintf(
            "✅ Terminé — %d élèves, %d parents, notes générées.",
            count($tousLesEleves),
            User::where('role', 'parent')->count()
        ));
    }

    // -----------------------------------------------------------------------
    // 1. ÉLÈVES
    // -----------------------------------------------------------------------

    /** Crée 10 élèves par classe et retourne le tableau de tous les IDs. */
    private function creerEleves(string $annee): array
    {
        $eleves = [];
        $classes = Classe::where('annee_scolaire', $annee)->orderBy('niveau')->get();

        foreach ($classes as $classe) {
            $existants = Eleve::where('classe_id', $classe->id)
                              ->where('annee_scolaire', $annee)
                              ->where('statut', 'actif')
                              ->count();

            $aCreer = self::EFFECTIF_PAR_CLASSE - $existants;
            if ($aCreer <= 0) {
                $this->command->line("  {$classe->nom} : déjà $existants élèves, rien à faire.");
                $ids = Eleve::where('classe_id', $classe->id)
                            ->where('annee_scolaire', $annee)
                            ->pluck('id')->toArray();
                $eleves = array_merge($eleves, $ids);
                continue;
            }

            $age = self::AGES_PAR_NIVEAU[$classe->niveau] ?? 8;

            for ($i = 0; $i < $aCreer; $i++) {
                $sexe = ($i % 2 === 0) ? 'M' : 'F'; // alternance M/F
                $prenom = $sexe === 'M'
                    ? self::PRENOMS_M[array_rand(self::PRENOMS_M)]
                    : self::PRENOMS_F[array_rand(self::PRENOMS_F)];
                $nom = self::NOMS_FAMILLE[array_rand(self::NOMS_FAMILLE)];

                $dateNaissance = now()
                    ->subYears($age)
                    ->addDays(rand(-180, 180))
                    ->format('Y-m-d');

                $estPere = rand(0, 9) < 7;
                $prenomParent = $estPere
                    ? self::PRENOMS_M[array_rand(self::PRENOMS_M)]
                    : self::PRENOMS_F[array_rand(self::PRENOMS_F)];

                $eleve = Eleve::create([
                    'matricule'         => Eleve::genererMatricule(),
                    'nom'               => $nom,
                    'prenom'            => $prenom,
                    'date_naissance'    => $dateNaissance,
                    'lieu_naissance'    => self::VILLES[array_rand(self::VILLES)],
                    'sexe'              => $sexe,
                    'nationalite'       => 'Burkinabè',
                    'classe_id'         => $classe->id,
                    'annee_scolaire'    => $annee,
                    'statut'            => 'actif',
                    'parent_nom'        => $nom,
                    'parent_prenom'     => $prenomParent,
                    'parent_telephone'  => $this->telephone(),
                    'parent_telephone2' => rand(0, 9) < 3 ? $this->telephone() : null,
                    'parent_adresse'    => self::VILLES[array_rand(self::VILLES)] . ', Secteur ' . rand(1, 30),
                    'parent_lien'       => $estPere ? 'père' : 'mère',
                ]);

                // Photo avatar générée immédiatement
                $eleve->update(['photo' => $this->genererAvatar($eleve)]);
                $eleves[] = $eleve->id;
            }

            $this->command->line("  {$classe->nom} : $aCreer élèves créés.");
        }

        return $eleves;
    }

    // -----------------------------------------------------------------------
    // 2. PARENTS (avec répartition précise)
    // -----------------------------------------------------------------------

    /**
     * Répartition exacte :
     *   3 parents × 4 enfants = 12 élèves
     *   8 parents × 3 enfants = 24 élèves
     *  10 parents × 2 enfants = 20 élèves
     *   4 parents × 1 enfant  =  4 élèves
     *                          ─────────
     *                   Total = 60 élèves / 25 parents
     */
    private function creerParents(array $eleveIds): void
    {
        // Mélange aléatoire (mais reproductible grâce à la graine)
        shuffle($eleveIds);

        $groupes = [
            ['nb_parents' => 3,  'enfants_chacun' => 4],   // 12 élèves
            ['nb_parents' => 8,  'enfants_chacun' => 3],   // 24 élèves
            ['nb_parents' => 10, 'enfants_chacun' => 2],   // 20 élèves
            ['nb_parents' => 4,  'enfants_chacun' => 1],   //  4 élèves
        ];

        $compteur = 0; // index courant dans $eleveIds
        $numParent = User::where('role', 'parent')->count() + 1;

        foreach ($groupes as $groupe) {
            for ($p = 0; $p < $groupe['nb_parents']; $p++) {
                // Tranche d'élèves pour ce parent
                $tranche = array_slice($eleveIds, $compteur, $groupe['enfants_chacun']);
                $compteur += $groupe['enfants_chacun'];

                if (empty($tranche)) {
                    break;
                }

                // Infos du parent basées sur le premier enfant
                $premierEleve = Eleve::find($tranche[0]);
                $email = 'parent' . $numParent . '@ecole.bf';

                // Évite les doublons si le seeder est rejoué
                if (User::where('email', $email)->exists()) {
                    $numParent++;
                    continue;
                }

                $parent = User::create([
                    'name'      => ($premierEleve->parent_prenom ?? 'Parent') . ' ' . ($premierEleve->nom ?? ''),
                    'email'     => $email,
                    'password'  => bcrypt('parent123'),
                    'role'      => 'parent',
                    'is_active' => true,
                ]);

                // Lien parent → enfants (table pivot parent_eleve)
                $parent->enfants()->attach($tranche);
                $numParent++;
            }
        }

        $this->command->line('  25 comptes parents créés (mot de passe : parent123).');
    }

    // -----------------------------------------------------------------------
    // 3. NOTES
    // -----------------------------------------------------------------------

    private function genererNotes(string $annee): void
    {
        $enseignantDefaut = User::where('role', '!=', 'parent')->first();
        $classes = Classe::where('annee_scolaire', $annee)->get();

        foreach ($classes as $classe) {
            $matieres = Matiere::where('classe_id', $classe->id)->where('is_active', true)->get();
            if ($matieres->isEmpty()) {
                continue;
            }

            $enseignant = User::where('role', 'enseignant')
                               ->where('classe_id', $classe->id)
                               ->first() ?? $enseignantDefaut;

            $eleves = Eleve::where('classe_id', $classe->id)
                           ->where('annee_scolaire', $annee)
                           ->where('statut', 'actif')
                           ->get();

            foreach ($eleves as $eleve) {
                // Niveau stable par élève (35% à 85%)
                $niveauBase = 35 + (crc32('niveau-' . $eleve->id) % 501) / 10;

                foreach ([1, 2, 3] as $trimestre) {
                    $niveauTrimestre = $niveauBase + rand(-5, 8) * ($trimestre - 1);

                    foreach ($matieres as $matiere) {
                        $existe = Note::where('eleve_id', $eleve->id)
                                      ->where('matiere_id', $matiere->id)
                                      ->where('trimestre', $trimestre)
                                      ->where('annee_scolaire', $annee)
                                      ->exists();
                        if ($existe) continue;

                        $pct = max(0, min(100, $niveauTrimestre + rand(-12, 12)));
                        $valeur = round(($pct / 100) * $matiere->bareme * 2) / 2;
                        $valeur = max(0, min($matiere->bareme, $valeur));

                        Note::create([
                            'eleve_id'       => $eleve->id,
                            'matiere_id'     => $matiere->id,
                            'valeur'         => $valeur,
                            'trimestre'      => $trimestre,
                            'annee_scolaire' => $annee,
                            'user_id'        => $enseignant->id,
                        ]);
                    }
                }
            }
            $this->command->line("  Notes {$classe->nom} : OK");
        }
    }

    // -----------------------------------------------------------------------
    // 4. AVATAR
    // -----------------------------------------------------------------------

    private function genererAvatar(Eleve $eleve): string
    {
        $taille = 320;
        $image  = imagecreatetruecolor($taille, $taille);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));

        [$r, $g, $b] = self::COULEURS_AVATAR[
            crc32($eleve->nom . $eleve->prenom) % count(self::COULEURS_AVATAR)
        ];
        imagefilledellipse(
            $image, $taille / 2, $taille / 2, $taille, $taille,
            imagecolorallocate($image, $r, $g, $b)
        );

        $initiales   = mb_strtoupper(mb_substr($eleve->nom, 0, 1) . mb_substr($eleve->prenom, 0, 1));
        $blanc       = imagecolorallocate($image, 255, 255, 255);
        $police      = resource_path('fonts/DejaVuSans-Bold.ttf');
        $taillePolice = 110;

        $boite = imagettfbbox($taillePolice, 0, $police, $initiales);
        $x = ($taille - ($boite[2] - $boite[0])) / 2;
        $y = ($taille + ($boite[1] - $boite[7])) / 2;
        imagettftext($image, $taillePolice, 0, (int)$x, (int)$y, $blanc, $police, $initiales);

        ob_start();
        imagepng($image);
        $contenu = ob_get_clean();
        imagedestroy($image);

        $chemin = 'photos/avatar-' . $eleve->matricule . '.png';
        Storage::disk('public')->put($chemin, $contenu);

        return $chemin;
    }

    private function telephone(): string
    {
        return '+226 ' . rand(50, 79) . ' ' .
               rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99);
    }
}
