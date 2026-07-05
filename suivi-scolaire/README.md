# 🏫 Système de Gestion de Scolarité et de Notes — Cycle Primaire

> Projet Web — UFR/SEA | Université Joseph Ki-Zerbo  
> Cours : Programmation Web et Framework  
> Enseignant : Lionel Marcus G. KABORET  
> Date de remise : 04/07/2026

---

## 👥 Membres du groupe

| Nom complet | Matricule |
|---|---|
| Sidibé Ramatou | [N02572220232] |
| SANDIWIDI Thierry Joel | [N00437620221] |

---

## 📋 Description du projet

Application web de gestion d'un établissement d'enseignement primaire (CP1 au CM2) permettant :
- Un suivi financier (inscriptions, paiements, reçus PDF)
- Un suivi pédagogique (notes, moyennes, classement, bulletins PDF)
- Un tableau de bord global (statistiques, impayés)
- Une gestion complète des classes, enseignants et élèves

---

## 🛠️ Technologies utilisées

- **Backend** : PHP 8.2 / Laravel 10
- **Frontend** : Blade, HTML5, CSS3, JavaScript
- **Base de données** : MySQL
- **PDF** : barryvdh/laravel-dompdf
- **Authentification** : Laravel Auth

---

## 🚀 Installation

### Prérequis
- PHP >= 8.2
- Composer
- MySQL
- Laragon ou XAMPP

### Étapes

```bash
# 1. Cloner le dépôt
git clone https://github.com/raynatou056-dot/suivi-scolaire.git
cd suivi-scolaire

# 2. Installer les dépendances
composer install

# 3. Copier le fichier d'environnement
cp .env.example .env

# 4. Générer la clé
php artisan key:generate

# 5. Configurer la base de données et l'année scolaire dans .env
DB_DATABASE=suivi-scolaire
DB_USERNAME=root
DB_PASSWORD=
ANNEE_SCOLAIRE=2025-2026

# 6. Créer la base de données dans MySQL
# Ouvrir phpMyAdmin et créer une base "suivi-scolaire"

# 7. Exécuter les migrations puis créer les comptes de démonstration
php artisan migrate
php artisan db:seed

# 8. Créer le lien vers le stockage public (photos des élèves, reçus, etc.)
php artisan storage:link

# 9. Lancer le serveur
php artisan serve
```

Accéder à l'application : **http://127.0.0.1:8000**

> 💡 `ANNEE_SCOLAIRE` centralise l'année scolaire utilisée par toute
> l'application (élèves, classes, notes, paiements). C'est la valeur de
> **secours** au premier démarrage ; une fois l'application utilisée, change
> plutôt l'année active depuis **Paramètres** dans l'interface (aucune
> modification de fichier ni redémarrage du serveur nécessaire). Voir aussi
> **Passage à l'année suivante** pour préparer la rentrée (promotion des
> élèves) avant d'activer la nouvelle année.

### Peupler un effectif complet (optionnel)

Pour remplir l'établissement avec un jeu de données réaliste — les 6 classes
du primaire avec le **programme réel par cycle** (CP1-CP2, CE1-CE2, CM1-CM2 —
voir la section Barème ci-dessus), un enseignant par classe et **25 élèves
par classe** (150 au total), toutes informations renseignées (élève + parent),
**avec une photo (avatar coloré + initiales) pour chacun, des notes
cohérentes sur les 3 trimestres (chacune respectant le barème de sa
matière), et un cahier de notes (devoirs) rempli pour chaque enseignant**
(de quoi tester moyennes, classement, bulletins et cahier de notes
immédiatement) :

```bash
php artisan db:seed --class=EffectifSeeder
```

Rejouable sans risque : il ne duplique rien (classes, matières et
enseignants existants sont conservés), et complète chaque classe jusqu'à 25
élèves seulement si elle en a moins. Si tu avais déjà lancé ce seeder avant
l'ajout des photos ou des notes, relance-le simplement : il complète ce qui
manque sans rien dupliquer, et ne touche jamais une note déjà saisie
manuellement.

---

## 🔑 Comptes de démonstration

Ces comptes sont créés par `php artisan db:seed` (voir `database/seeders/DatabaseSeeder.php`) :

| Rôle | Email | Mot de passe | Accès |
|---|---|---|---|
| 👨‍💼 Gestionnaire | admin@ecole.bf | admin123 | Accès complet (élèves, classes, paiements, enseignants, notes) |
| 👨‍👩‍👧 Parent (démo) | parent@ecole.bf | parent123 | Accès à l'**API mobile** uniquement (pas à l'interface web) — voir un enfant (Ramatou Sidibé, classe CP1) déjà lié à ce compte |

> Le seeder ne crée pas de compte Enseignant par défaut. Pour en créer un (ou
> tester un effectif complet avec un enseignant par classe), utilise le menu
> *Enseignants* une fois connecté en Gestionnaire, ou lance
> `php artisan db:seed --class=EffectifSeeder`.

> Le seeder crée maintenant un jeu de données prêt à explorer dès la première
> connexion : une classe CP1 avec 2 matières, un élève avec des notes, une
> absence et deux annonces — pas besoin de tout créer à la main pour tester.

---

## ✅ Fonctionnalités

### 🔐 Authentification & rôles
- Connexion sécurisée par email et mot de passe (limitée à 5 tentatives/minute)
- Deux rôles : **Gestionnaire** et **Enseignant**, appliqués au niveau des routes
  via le middleware `role` (`app/Http/Middleware/EnsureRole.php`) — un Enseignant
  qui tente d'accéder à une page réservée au Gestionnaire reçoit une erreur 403,
  il ne s'agit pas seulement de cacher des liens dans le menu.
- Un Enseignant ne voit et ne modifie que les notes des élèves de **sa propre
  classe** (vérifié à la fois côté affichage et côté enregistrement dans
  `NoteController`), même en forgeant l'URL ou l'identifiant d'un élève.

### 👨‍🎓 Gestion des Élèves
- Inscription des élèves avec photo
- Liste des élèves avec recherche et filtres
- Voir le dossier complet d'un élève
- Modifier les informations d'un élève
- Supprimer un élève

### 🏫 Gestion des Classes
- Configuration des 6 niveaux (CP1 à CM2)
- Définition des frais de scolarité par classe
- Ajout et modification des matières avec coefficients
- Vue détaillée de chaque classe

### 👨‍🏫 Gestion des Enseignants
- Création de comptes enseignants
- Assignation d'une classe à chaque enseignant
- Modification et suppression des comptes
- Activation/désactivation des comptes

### 💰 Gestion Financière
- Enregistrement des paiements (espèces, mobile money, virement, chèque)
- Calcul automatique du reste à payer
- Génération de reçus PDF — présentation soignée façon document officiel
  (cadre, en-tête avec logo/adresse/téléphone de l'établissement, montant
  écrit en chiffres **et en toutes lettres**, cases cachet/signature)
- Liste des élèves en retard de paiement

### 📚 Gestion Pédagogique
- Saisie des notes par matière et par trimestre
- **Barème par matière** : chaque matière est notée sur 10 ou sur 20 (voir
  `Matiere::bareme`) — au primaire, du CP1 au CE2 tout est noté sur 10 ; au
  CM1/CM2, Étude de texte, Problème, Opération, Sciences et
  Histoire-Géographie sont notées sur 20, le reste reste sur 10
- **La moyenne trimestrielle est toujours exprimée sur 10**, quel que soit
  le mélange de barèmes des matières qui la composent (chaque note est
  ramenée sur 10 avant d'être pondérée par son coefficient — voir
  `MoyenneService`), conformément à la pratique du primaire burkinabè
- Calcul automatique des moyennes avec coefficients
- Classement des élèves par classe
- Génération de bulletins de notes PDF — même présentation officielle que
  les reçus (cadre, photo de l'élève ou ses initiales, appréciation
  générale calculée automatiquement à partir de la moyenne, cases
  cachet/signature) ; chaque note y affiche son propre barème, ex. *14/20*
  ou *7/10*
- **Cahier de notes** : chaque enseignant compose ses devoirs pour sa classe
  (notés ou simples consignes sans note), avec une page de saisie dédiée par
  devoir et le suivi de la progression (combien d'élèves déjà notés, moyenne
  de la classe sur ce devoir, toujours rapportée au barème de sa matière)

### 🚫 Suivi des absences
- Feuille de présence quotidienne par classe (cases à cocher + motif/justification)
- Historique des absences consultable par classe
- Un enseignant ne gère que les absences de sa propre classe

### 📢 Annonces & notifications
- Publication d'annonces (information, examen, réunion, échéance de paiement)
- Une annonce cible soit toute l'école, soit une classe précise
- Un enseignant ne peut publier que pour sa propre classe ; le gestionnaire peut cibler n'importe quelle classe ou toute l'école
- Consommées par l'application mobile Parent, avec suivi lu/non lu

### 👨‍👩‍👧 Comptes parents (application mobile)
- Le Gestionnaire crée les comptes parents et les associe à un ou plusieurs enfants
- Un parent se connecte uniquement via l'**API mobile** (voir plus bas), jamais via l'interface web Gestionnaire/Enseignant
- Modification de mot de passe depuis l'application mobile

### 🎓 Passage à l'année scolaire suivante
- Pour chaque classe, décide pour chaque élève : **promu** en classe
  supérieure, **redouble**, ou **quitte l'établissement** (devient
  "diplômé" pour un CM2, "transféré" sinon)
- Crée automatiquement les classes de l'année suivante (en reprenant frais
  et matières de la classe de référence de l'année en cours) et y inscrit
  les élèves promus/redoublants avec un nouveau dossier
- Les notes, paiements, absences et devoirs de l'année précédente restent
  intacts et consultables — rien n'est jamais supprimé
- Le compte parent suit automatiquement son enfant vers son nouveau dossier
- Rejouable sans risque : un élève déjà traité est ignoré (pas de doublon)

### ⚙️ Paramètres de l'établissement
- Change l'année scolaire active **depuis l'interface**, sans modifier le
  fichier `.env` ni relancer le serveur — pratique après un passage d'année
- Change le nom de l'établissement, ainsi que son adresse et son téléphone
  (facultatifs) **depuis l'interface** — affichés sur les bulletins, les
  reçus de paiement et l'en-tête de l'application (sidebar, page de
  connexion). Valeurs par défaut dans `.env` : `NOM_ECOLE`,
  `ADRESSE_ECOLE`, `TELEPHONE_ECOLE`.

### 📊 Tableau de Bord
- Statistiques globales (élèves, classes, frais)
- Taux de recouvrement des frais avec barre de progression
- Récapitulatif financier par classe
- Liste des élèves avec impayés (aperçu — voir la page dédiée ci-dessous)
- Paiements récents

### ⚠️ Impayés (page dédiée)
- Liste complète (pas juste un aperçu) de tous les élèves en solde dû,
  triée par montant restant, avec recherche et filtre par classe
- Montant total dû affiché en un coup d'œil
- Accès direct à l'encaissement ou au dossier de l'élève

### 📚 Matières (page dédiée)
- Vue d'ensemble de toutes les matières de l'année, classe par classe
- Création, modification et suppression indépendantes du formulaire de
  classe (qui reste aussi disponible pour une gestion rapide à la création
  d'une classe)
- Activer/désactiver une matière sans supprimer ses notes déjà saisies

---

## 🗄️ Structure de la base de données

| Table | Contenu |
|---|---|
| users | Comptes gestionnaire, enseignants **et parents** |
| classes | Les 6 niveaux CP1 à CM2 |
| eleves | Les élèves inscrits |
| paiements | Les versements des parents |
| matieres | Les matières par classe, avec coefficient et barème (10 ou 20) |
| notes | Les notes par élève, matière et trimestre |
| devoirs | Les devoirs composés par chaque enseignant (notés ou non) |
| devoir_notes | La note de chaque élève pour un devoir donné |
| parent_eleve | Lie un compte parent à un ou plusieurs enfants |
| absences | Les absences par élève, avec motif et justification |
| annonces | Les annonces publiées (toute l'école ou une classe) |
| annonce_lectures | Suivi lu/non lu par parent, pour l'application mobile |
| personal_access_tokens | Jetons d'authentification API (Laravel Sanctum) pour l'app mobile |
| parametres | Réglages modifiables depuis l'interface (ex: année scolaire active) |

---

## 📁 Organisation du code

```
app/
├── Http/
│   ├── Controllers/      # Un contrôleur par ressource (Eleve, Classe, Paiement, Note, Enseignant…)
│   │   └── Api/          # Contrôleurs de l'API mobile Parent (auth, enfants, notes, paiements...)
│   ├── Requests/         # Validation extraite des contrôleurs (StoreEleveRequest, StorePaiementRequest…)
│   │   └── Api/          # Requests de l'API mobile (LoginApiRequest, ChangePasswordRequest)
│   ├── Resources/        # Sérialisation JSON de l'API (EleveResource, AnnonceResource…)
│   └── Middleware/
│       └── EnsureRole.php   # Sépare les accès Gestionnaire / Enseignant / Parent au niveau des routes
├── Models/               # User, Eleve, Classe, Matiere, Note, Paiement, Absence, Annonce (relations Eloquent)
├── Services/
│   └── MoyenneService.php  # Calcul de la moyenne et du rang, partagé par le web ET l'API mobile
└── Providers/

resources/
└── views/
    ├── layouts/          # Mise en page commune (sidebar, topbar, pagination) + design system CSS
    ├── admin/            # Élèves, classes, enseignants, parents, paiements, notes, absences, annonces, dashboard
    ├── teacher/           # Tableau de bord simplifié pour les enseignants
    └── auth/             # Connexion

public/assets/            # CSS / JS du design system (aucune dépendance externe, aucune étape de build)

routes/
├── web.php               # Routes de l'interface Gestionnaire/Enseignant, regroupées par rôle requis
└── api.php               # Routes de l'API mobile Parent (authentification par jeton Sanctum)

tests/Feature/            # Tests automatisés (voir ci-dessous)
└── Api/                  # Tests de l'API mobile
```

### Modèles et relations principales

- `Classe` → `hasMany` Eleve, `hasMany` Matiere, `hasMany` Devoir
- `Eleve` → `belongsTo` Classe, `hasMany` Paiement, `hasMany` Note, `hasMany` Absence,
  `belongsToMany` User (ses parents, via `parent_eleve`) — suppression douce via `SoftDeletes`
- `User` (Gestionnaire, Enseignant ou Parent) → `belongsTo` Classe (pour un Enseignant) ;
  `belongsToMany` Eleve (ses enfants, pour un Parent, via `parent_eleve`) ; `HasApiTokens` (Sanctum)
- `Note` → `belongsTo` Eleve, `belongsTo` Matiere
- `Devoir` → `belongsTo` Classe, Matiere, User (l'enseignant) ; `hasMany` DevoirNote
- `DevoirNote` → `belongsTo` Devoir, Eleve (une ligne = la note d'un élève pour un devoir)
- `Absence` → `belongsTo` Eleve, `belongsTo` User (qui l'a enregistrée)
- `Annonce` → `belongsTo` Classe (nullable = toute l'école), `hasMany` AnnonceLecture
- `Paiement` → `belongsTo` Eleve, `belongsTo` User (qui a enregistré le versement)

---

## 📱 API REST — Application mobile Parent

Une API REST dédiée (préfixe `/api`, authentification par jeton **Laravel
Sanctum**) permet à une application mobile (Android) de donner accès aux
parents au suivi de leurs enfants. Cette API est **réservée aux comptes de
rôle `parent`** — un compte Gestionnaire ou Enseignant ne peut pas s'y
connecter.

### Authentification

Toutes les routes (sauf `/login`) nécessitent l'en-tête :
```
Authorization: Bearer <token>
```
obtenu via `/login`. Le jeton n'expire pas tant qu'il n'est pas révoqué via `/logout`.

| Méthode | Route | Description |
|---|---|---|
| POST | `/api/login` | `{email, password}` → `{token, parent, enfants}` |
| POST | `/api/logout` | Révoque le jeton utilisé pour la requête |
| PUT  | `/api/password` | `{current_password, password, password_confirmation}` |

### Données d'un enfant

| Méthode | Route | Description |
|---|---|---|
| GET | `/api/enfants` | Liste des enfants du parent connecté |
| GET | `/api/enfants/{id}?trimestre=1` | Infos, moyenne générale, rang, dernières notes |
| GET | `/api/enfants/{id}/notes` | Notes par matière, regroupées par trimestre (1, 2, 3) avec moyenne de chacun |
| GET | `/api/enfants/{id}/paiements` | Historique, total payé, reste à payer |
| GET | `/api/enfants/{id}/paiements/{paiement}/recu` | Téléchargement du reçu PDF |
| GET | `/api/enfants/{id}/absences` | Liste des absences avec motif et justification |

### Annonces

| Méthode | Route | Description |
|---|---|---|
| GET | `/api/annonces` | Annonces de l'école + de la classe de chaque enfant, avec compteur `non_lues` |
| POST | `/api/annonces/{id}/lue` | Marque une annonce comme lue |

> Toute tentative d'un parent d'accéder aux données d'un élève qui n'est pas
> le sien renvoie une erreur **403**, même en changeant l'identifiant dans
> l'URL (vérifié par `VerifiesParentAccess` et testé automatiquement).

### Exemple (cURL)

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" \
  -d "email=parent@ecole.bf&password=parent123"

curl http://127.0.0.1:8000/api/enfants \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token reçu ci-dessus>"
```

---

## 🧪 Tests automatisés

Le projet inclut une suite de tests Feature (PHPUnit) qui couvre :

- l'authentification et le contrôle d'accès par rôle (`AuthAndRolesTest`) ;
- les parcours d'administration — création/modification/suppression d'élèves,
  classes, enseignants, paiements, saisie de notes — avec leurs règles de
  validation (`GestionAdministrativeTest`) ;
- le tableau de bord, le reçu de paiement et le bulletin PDF
  (`DashboardEtDocumentsTest`) ;
- le cahier de notes / devoirs (`DevoirsTest`) ;
- les absences, annonces et comptes parents côté web (`SuiviParentTest`) ;
- l'API REST mobile complète : connexion, sécurité par jeton, accès aux
  notes/paiements/absences/annonces, isolation stricte entre les enfants des
  différents parents (`Api\ParentApiTest`) ;
- le changement de mot de passe et le parcours complet « mot de passe
  oublié » par email (`PasswordManagementTest`) ;
- le passage à l'année scolaire suivante et le changement d'année active
  (`PassageAnneeTest`) ;
- les pages dédiées Matières et Impayés (`MatieresEtImpayesTest`).

Au total, **108 tests / 364 assertions**, tous exécutés contre une vraie base
de données (SQLite en mémoire) à chaque modification du projet.

Pour les exécuter (une base SQLite en mémoire est utilisée automatiquement,
aucune configuration MySQL n'est nécessaire pour lancer les tests) :

```bash
php artisan test
# ou directement :
php vendor/bin/phpunit
```

---

## 🔒 Notes de sécurité

- Les mots de passe sont hachés avec Bcrypt (`Hash::make`).
- Chaque formulaire est protégé par un jeton CSRF (`@csrf`).
- La connexion est limitée à 5 tentatives par minute par IP (`throttle:5,1`).
- L'accès aux pages d'administration est vérifié côté serveur (middleware
  `role`), pas seulement masqué dans l'interface.
- Un compte désactivé (`is_active = false`) ne peut pas se connecter, même
  avec le bon mot de passe.
- L'API mobile utilise des jetons Sanctum (table `personal_access_tokens`) :
  révocables individuellement à la déconnexion, sans jamais faire circuler le
  mot de passe après la connexion initiale.
- Un parent ne peut consulter ou télécharger que les données de ses propres
  enfants : tout identifiant d'élève qui ne lui appartient pas renvoie une
  erreur 403, vérifié par `VerifiesParentAccess` sur chaque route concernée.
- Seuls les comptes de rôle `parent` peuvent se connecter via `/api/login` —
  un compte Gestionnaire ou Enseignant y est explicitement refusé.
- Tout utilisateur connecté (Gestionnaire ou Enseignant) peut changer son
  propre mot de passe (icône 🔑 dans la barre du haut). En cas de mot de
  passe oublié, un lien de réinitialisation par email est disponible depuis
  la page de connexion — entièrement en français, y compris le contenu de
  l'email (voir `lang/fr.json`).

### Configurer l'envoi d'emails (mot de passe oublié)

Par défaut (`MAIL_MAILER=log`), les emails ne sont pas réellement envoyés :
leur contenu est simplement écrit dans `storage/logs/laravel.log`, pratique
pour tester en local sans rien configurer. Pour un vrai envoi en production,
remplace dans `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=ton-serveur-smtp
MAIL_PORT=587
MAIL_USERNAME=ton-compte
MAIL_PASSWORD=ton-mot-de-passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="ecole@tondomaine.bf"
```
