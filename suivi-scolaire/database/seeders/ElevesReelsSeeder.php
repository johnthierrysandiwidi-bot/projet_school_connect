<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Inscrit 10 élèves réels par classe (CP1 → CM2) avec :
 *  - NOM en premier, puis prénom (convention burkinabè)
 *  - Une photo avatar générée automatiquement
 *  - Des notes pour les 3 trimestres, par matière
 *
 * php artisan db:seed --class=ElevesReelsSeeder
 */
class ElevesReelsSeeder extends Seeder
{
    /* ------------------------------------------------------------------ */
    /* LISTE DES ÉLÈVES — format ['NOM', 'Prénom', 'M/F', parent_nom, parent_prenom, téléphone] */
    /* ------------------------------------------------------------------ */

    private const ELEVES = [
        'CP1' => [
            ['KABORE',      'Fatimata',  'F', 'KABORE',      'Adama',     '70112233'],
            ['SAWADOGO',    'Issa',      'M', 'SAWADOGO',    'Moussa',    '76223344'],
            ['TRAORE',      'Aminata',   'F', 'TRAORE',      'Ibrahim',   '74334455'],
            ['OUEDRAOGO',   'Boukary',   'M', 'OUEDRAOGO',   'Seydou',    '71445566'],
            ['ZONGO',       'Ramatou',   'F', 'ZONGO',       'Hamidou',   '70556677'],
            ['DIALLO',      'Salif',     'M', 'DIALLO',      'Abdoulaye', '76667788'],
            ['COMPAORE',    'Aïcha',     'F', 'COMPAORE',    'Karim',     '74778899'],
            ['NIKIEMA',     'Drissa',    'M', 'NIKIEMA',     'Idrissa',   '71889900'],
            ['YAMEOGO',     'Mariam',    'F', 'YAMEOGO',     'Lassané',   '70990011'],
            ['ILBOUDO',     'Inoussa',   'M', 'ILBOUDO',     'Souleymane','76001122'],
        ],
        'CP2' => [
            ['BATIONO',     'Salimata',  'F', 'BATIONO',     'Yacouba',   '70111222'],
            ['OUATTARA',    'Brahima',   'M', 'OUATTARA',    'Rasmané',   '76222333'],
            ['SAVADOGO',    'Djénéba',   'F', 'SAVADOGO',    'Paul',      '74333444'],
            ['TAPSOBA',     'Rasmané',   'M', 'TAPSOBA',     'Issa',      '71444555'],
            ['BELEM',       'Hadiza',    'F', 'BELEM',       'Ousmane',   '70555666'],
            ['GUIGMA',      'Lassané',   'M', 'GUIGMA',      'Boukary',   '76666777'],
            ['YARO',        'Assita',    'F', 'YARO',        'Adama',     '74777888'],
            ['PAFADNAM',    'Seydou',    'M', 'PAFADNAM',    'Moussa',    '71888999'],
            ['BALIMA',      'Lucie',     'F', 'BALIMA',      'Hamidou',   '70999000'],
            ['BOUDA',       'Hamidou',   'M', 'BOUDA',       'Ibrahim',   '76000111'],
        ],
        'CE1' => [
            ['TIENDREBEOGO','Awa',        'F','TIENDREBEOGO','Karim',     '70121212'],
            ['KIENTEGA',    'Moussa',     'M','KIENTEGA',    'Salif',     '76232323'],
            ['COULIBALY',   'Bintou',     'F','COULIBALY',   'Drissa',    '74343434'],
            ['KONATE',      'Abdoulaye',  'M','KONATE',      'Idrissa',   '71454545'],
            ['SANGARE',     'Korotoum',   'F','SANGARE',     'Lassané',   '70565656'],
            ['BAMOGO',      'Ibrahim',    'M','BAMOGO',      'Souleymane','76676767'],
            ['SANOU',       'Zalia',      'F','SANOU',       'Yacouba',   '74787878'],
            ['COMPAORE',    'Idrissa',    'M','COMPAORE',    'Brahima',   '71898989'],
            ['DARE',        'Pauline',    'F','DARE',        'Rasmané',   '70909090'],
            ['KABORE',      'Karim',      'M','KABORE',      'Paul',      '76010101'],
        ],
        'CE2' => [
            ['OUEDRAOGO',   'Nathalie',   'F','OUEDRAOGO',  'Issa',      '70131313'],
            ['SAWADOGO',    'Souleymane', 'M','SAWADOGO',   'Boukary',   '76242424'],
            ['TRAORE',      'Adjara',     'F','TRAORE',     'Adama',     '74353535'],
            ['ZONGO',       'Brahima',    'M','ZONGO',      'Moussa',    '71464646'],
            ['DIALLO',      'Habibou',    'F','DIALLO',     'Hamidou',   '70575757'],
            ['NIKIEMA',     'Salif',      'M','NIKIEMA',    'Abdoulaye', '76686868'],
            ['YAMEOGO',     'Saratou',    'F','YAMEOGO',    'Karim',     '74797979'],
            ['ILBOUDO',     'Yacouba',    'M','ILBOUDO',    'Drissa',    '71808080'],
            ['BATIONO',     'Rasmata',    'F','BATIONO',    'Idrissa',   '70818181'],
            ['TAPSOBA',     'Rasmané',    'M','TAPSOBA',    'Lassané',   '76929292'],
        ],
        'CM1' => [
            ['BALIMA',      'Fatimata',   'F','BALIMA',     'Souleymane','70141414'],
            ['BOUDA',       'Issa',       'M','BOUDA',      'Yacouba',   '76252525'],
            ['GUIGMA',      'Aminata',    'F','GUIGMA',     'Brahima',   '74363636'],
            ['YARO',        'Moussa',     'M','YARO',       'Rasmané',   '71474747'],
            ['PAFADNAM',    'Mariam',     'F','PAFADNAM',   'Paul',      '70585858'],
            ['KABORE',      'Hamidou',    'M','KABORE',     'Issa',      '76696969'],
            ['SAWADOGO',    'Aïcha',      'F','SAWADOGO',   'Boukary',   '74707070'],
            ['TRAORE',      'Abdoulaye',  'M','TRAORE',     'Adama',     '71717171'],
            ['OUEDRAOGO',   'Rasmata',    'F','OUEDRAOGO',  'Moussa',    '70828282'],
            ['ZONGO',       'Seydou',     'M','ZONGO',      'Hamidou',   '76939393'],
        ],
        'CM2' => [
            ['DIALLO',      'Salimata',   'F','DIALLO',     'Abdoulaye', '70151515'],
            ['NIKIEMA',     'Brahima',    'M','NIKIEMA',    'Karim',     '76262626'],
            ['YAMEOGO',     'Djénéba',    'F','YAMEOGO',    'Drissa',    '74373737'],
            ['ILBOUDO',     'Lassané',    'M','ILBOUDO',    'Idrissa',   '71484848'],
            ['BATIONO',     'Habibou',    'F','BATIONO',    'Lassané',   '70595959'],
            ['OUATTARA',    'Rasmané',    'M','OUATTARA',   'Souleymane','76606060'],
            ['SAVADOGO',    'Assita',     'F','SAVADOGO',   'Yacouba',   '74717171'],
            ['TAPSOBA',     'Salif',      'M','TAPSOBA',    'Brahima',   '71828282'],
            ['BELEM',       'Korotoum',   'F','BELEM',      'Rasmané',   '70939393'],
            ['KIENTEGA',    'Inoussa',    'M','KIENTEGA',   'Paul',      '76040404'],
        ],
    ];

    /* Niveaux moyens réalistes par élève (index dans la liste de la classe) */
    private const NIVEAUX = [
        0 => 78, 1 => 65, 2 => 82, 3 => 71, 4 => 55,
        5 => 88, 6 => 60, 7 => 74, 8 => 68, 9 => 85,
    ];

    private const COULEURS = [
        [26,86,219],[5,150,105],[217,119,6],[185,28,28],
        [124,58,237],[219,39,119],[13,148,136],[202,138,4],
        [30,64,175],[4,120,87],
    ];

    public function run(): void
    {
        $annee = config('app.annee_scolaire');
        $admin = User::where('role', 'gestionnaire')->first();

        foreach (self::ELEVES as $niveau => $liste) {
            $classe = Classe::where('niveau', $niveau)
                            ->where('annee_scolaire', $annee)
                            ->first();

            if (!$classe) {
                $this->command->warn("Classe $niveau introuvable — crée-la d'abord.");
                continue;
            }

            $matieres = Matiere::where('classe_id', $classe->id)
                               ->where('is_active', true)
                               ->get();

            $enseignant = User::where('role', 'enseignant')
                               ->where('classe_id', $classe->id)
                               ->first() ?? $admin;

            foreach ($liste as $idx => [$nom, $prenom, $sexe, $pNom, $pPrenom, $tel]) {
                // Ne pas créer deux fois le même élève
                $existe = Eleve::where('nom', $nom)
                               ->where('prenom', $prenom)
                               ->where('classe_id', $classe->id)
                               ->exists();
                if ($existe) {
                    $this->command->line("  Déjà inscrit : $nom $prenom ($niveau)");
                    continue;
                }

                $ageNominal = ['CP1'=>6,'CP2'=>7,'CE1'=>8,'CE2'=>9,'CM1'=>10,'CM2'=>11][$niveau] ?? 8;
                $dateNais = now()->subYears($ageNominal)->addDays(rand(-150,150))->format('Y-m-d');

                $estPere = ($sexe === 'M') ? true : (rand(0,1) === 1);

                $eleve = Eleve::create([
                    'matricule'        => Eleve::genererMatricule(),
                    'nom'              => $nom,
                    'prenom'           => $prenom,
                    'date_naissance'   => $dateNais,
                    'lieu_naissance'   => 'Ouagadougou',
                    'sexe'             => $sexe,
                    'nationalite'      => 'Burkinabè',
                    'classe_id'        => $classe->id,
                    'annee_scolaire'   => $annee,
                    'statut'           => 'actif',
                    'parent_nom'       => $pNom,
                    'parent_prenom'    => $pPrenom,
                    'parent_telephone' => '+226 ' . $tel,
                    'parent_lien'      => $estPere ? 'père' : 'mère',
                ]);

                $eleve->update(['photo' => $this->avatar($eleve, $idx)]);

                // Notes pour les 3 trimestres
                $niveauBase = self::NIVEAUX[$idx] ?? 65;
                foreach ([1,2,3] as $trimestre) {
                    $nvTrim = $niveauBase + rand(-4, 6) * ($trimestre - 1);
                    foreach ($matieres as $matiere) {
                        $pct  = max(10, min(100, $nvTrim + rand(-10,10)));
                        $val  = round(($pct / 100) * $matiere->bareme * 2) / 2;
                        $val  = max(0, min($matiere->bareme, $val));
                        Note::firstOrCreate(
                            ['eleve_id'=>$eleve->id,'matiere_id'=>$matiere->id,
                             'trimestre'=>$trimestre,'annee_scolaire'=>$annee],
                            ['valeur'=>$val,'user_id'=>$enseignant->id]
                        );
                    }
                }
            }

            $nbEleves = Eleve::where('classe_id',$classe->id)->where('annee_scolaire',$annee)->count();
            $this->command->info("✓ $niveau : $nbEleves élèves inscrits avec notes.");
        }

        $this->command->info("\n✅ Terminé — ".Eleve::where('annee_scolaire',$annee)->count()." élèves au total.");
    }

    private function avatar(Eleve $eleve, int $idx): string
    {
        $taille = 320;
        $img    = imagecreatetruecolor($taille, $taille);
        imagesavealpha($img, true);
        imagefill($img, 0, 0, imagecolorallocatealpha($img,0,0,0,127));

        [$r,$g,$b] = self::COULEURS[$idx % count(self::COULEURS)];
        imagefilledellipse($img,$taille/2,$taille/2,$taille,$taille,imagecolorallocate($img,$r,$g,$b));

        // Initiales : N P (NOM Prénom)
        $init   = mb_strtoupper(mb_substr($eleve->nom,0,1).mb_substr($eleve->prenom,0,1));
        $blanc  = imagecolorallocate($img,255,255,255);
        $police = resource_path('fonts/DejaVuSans-Bold.ttf');
        $sz     = 100;
        $boite  = imagettfbbox($sz,0,$police,$init);
        $x = ($taille - ($boite[2]-$boite[0])) / 2;
        $y = ($taille + ($boite[1]-$boite[7])) / 2;
        imagettftext($img,$sz,0,(int)$x,(int)$y,$blanc,$police,$init);

        ob_start(); imagepng($img); $data = ob_get_clean(); imagedestroy($img);
        $chemin = 'photos/avatar-'.$eleve->matricule.'.png';
        Storage::disk('public')->put($chemin,$data);
        return $chemin;
    }
}
