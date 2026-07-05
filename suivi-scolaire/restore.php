<?php
// Script de restauration — lance avec : php artisan tinker --execute="require 'restore.php';"
// OU directement avec : php restore.php (depuis le dossier du projet)

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Classe;
use App\Models\Matiere;
use App\Models\User;

$annee = config('app.annee_scolaire');
echo "Annee scolaire : $annee\n\n";

// ---- 1. CLASSES ----
$classesData = [
    ['niveau' => 'CP1', 'nom' => 'CP1', 'frais_scolarite' => 30000],
    ['niveau' => 'CP2', 'nom' => 'CP2', 'frais_scolarite' => 35000],
    ['niveau' => 'CE1', 'nom' => 'CE1', 'frais_scolarite' => 40000],
    ['niveau' => 'CE2', 'nom' => 'CE2', 'frais_scolarite' => 45000],
    ['niveau' => 'CM1', 'nom' => 'CM1', 'frais_scolarite' => 50000],
    ['niveau' => 'CM2', 'nom' => 'CM2', 'frais_scolarite' => 60000],
];

$classes = [];
foreach ($classesData as $data) {
    $c = Classe::firstOrCreate(
        ['niveau' => $data['niveau'], 'annee_scolaire' => $annee],
        ['nom' => $data['nom'], 'frais_scolarite' => $data['frais_scolarite'], 'capacite_max' => 40]
    );
    $classes[$data['niveau']] = $c;
    echo "OK Classe {$data['nom']} (id={$c->id})\n";
}

// ---- 2. MATIERES ----
$matieresCp = ['Dictee', 'Education morale', 'Ecriture', 'Copie', 'Calcul', 'Lecture', 'Dessin', 'Recitation-Chant', 'Anglais'];
$matieresCe = ['Dictee', 'Education civique et morale', 'Grammaire', 'Vocabulaire', 'Orthographe', 'Expression ecrite', "Exercice d'observation", 'Histoire', 'Geographie', 'Probleme', 'Operation', 'Lecture', 'Dessin', 'Recitation-Chant', 'Anglais'];
$matieresCm = ['Dictee', 'Redaction', 'Etude de texte', 'Histoire-Geographie', 'Science', 'Operation', 'Probleme', 'Education civique et morale', 'Dessin', 'Recitation-Chant', 'Lecture', 'Anglais'];

// Noms corrects avec accents
$nomsCorrects = [
    'Dictee' => 'Dictée',
    'Education morale' => 'Éducation morale',
    'Ecriture' => 'Écriture',
    'Copie' => 'Copie',
    'Calcul' => 'Calcul',
    'Lecture' => 'Lecture',
    'Dessin' => 'Dessin',
    'Recitation-Chant' => 'Récitation/Chant',
    'Anglais' => 'Anglais',
    'Education civique et morale' => 'Éducation civique et morale',
    'Grammaire' => 'Grammaire',
    'Vocabulaire' => 'Vocabulaire',
    'Orthographe' => 'Orthographe',
    'Expression ecrite' => 'Expression écrite',
    "Exercice d'observation" => "Exercice d'observation",
    'Histoire' => 'Histoire',
    'Geographie' => 'Géographie',
    'Probleme' => 'Problème',
    'Operation' => 'Opération',
    'Redaction' => 'Rédaction',
    'Etude de texte' => 'Étude de texte',
    'Histoire-Geographie' => 'Histoire-Géographie',
    'Science' => 'Science',
];

$mapping = [
    'CP1' => $matieresCp,
    'CP2' => $matieresCp,
    'CE1' => $matieresCe,
    'CE2' => $matieresCe,
    'CM1' => $matieresCm,
    'CM2' => $matieresCm,
];

foreach ($mapping as $niveau => $matieres) {
    $count = 0;
    foreach ($matieres as $cle) {
        $nom = $nomsCorrects[$cle] ?? $cle;
        Matiere::firstOrCreate(
            ['nom' => $nom, 'classe_id' => $classes[$niveau]->id],
            ['coefficient' => 1, 'bareme' => 10, 'is_active' => true]
        );
        $count++;
    }
    echo "OK $count matieres pour $niveau\n";
}

// ---- 3. ENSEIGNANTS ----
$enseignants = [
    ['name' => 'Bado Aristide',   'email' => 'enseignantcp1@ecole.bf', 'niveau' => 'CP1'],
    ['name' => 'Guinko Rasack',   'email' => 'enseignantcp2@ecole.bf', 'niveau' => 'CP2'],
    ['name' => 'Yougbare Pascal', 'email' => 'enseignantce1@ecole.bf', 'niveau' => 'CE1'],
    ['name' => 'Bado Cedric',     'email' => 'enseignantce2@ecole.bf', 'niveau' => 'CE2'],
    ['name' => 'Sorgho Dramane',  'email' => 'enseignantcm1@ecole.bf', 'niveau' => 'CM1'],
    ['name' => 'Pitenga Romaric', 'email' => 'enseignantcm2@ecole.bf', 'niveau' => 'CM2'],
];

foreach ($enseignants as $e) {
    $exists = User::where('email', $e['email'])->exists();
    if (!$exists) {
        User::create([
            'name'      => $e['name'],
            'email'     => $e['email'],
            'password'  => bcrypt('enseignant123'),
            'role'      => 'enseignant',
            'classe_id' => $classes[$e['niveau']]->id,
            'is_active' => true,
        ]);
        echo "OK Enseignant {$e['name']}\n";
    } else {
        echo "EXISTE DEJA : {$e['name']}\n";
    }
}

echo "\nTERMINE ! 6 classes + matieres + 6 enseignants crees.\n";
echo "Mot de passe enseignants : enseignant123\n";
