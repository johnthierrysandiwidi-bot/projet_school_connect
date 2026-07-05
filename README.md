# 📱 School Connect — Application Mobile de Suivi Scolaire Parent-Enfant

> Projet - Développement Mobile | Université Joseph Ki-Zerbo — UFR/SEA  
> Enseignant : Lionel Marcus G. KABORET | Juin 2026

---

## 👥 Membres du groupe

| Nom & Prénom | Numéro étudiant |
|---|---|
| SIDIBE Ramatou | N02572220232 |
| SANDIWIDI Thierry Joël | N00437620221 |

---

## 📋 Sujet

L'objectif de ce projet est de concevoir une application mobile permettant aux parents d'élèves de suivre la scolarité de leurs enfants dans un établissement primaire (du CP1 au CM2). L'application offre une interface simple et intuitive pour consulter les informations académiques, financières et administratives en temps réel, en consommant une API REST développée avec Laravel.

---

## 🛠️ Technologies utilisées

### Application mobile
| Technologie | Version | Rôle |
|---|---|---|
| Kotlin | 1.9.20 | Langage principal |
| Android SDK | 34 (Android 14) | Plateforme cible |
| Android minimum | 26 (Android 8.0) | Version minimale supportée |
| Retrofit 2 | 2.9.0 | Appels API REST |
| Gson | 2.9.0 | Désérialisation JSON |
| OkHttp | 4.12.0 | Client HTTP + logs réseau |
| Glide | 4.16.0 | Chargement des photos des élèves |
| Coroutines | 1.7.3 | Programmation asynchrone |
| ViewModel + LiveData | 2.7.0 | Architecture MVVM |
| ViewBinding | — | Liaison des vues XML |

### Backend (API REST)
| Technologie | Rôle |
|---|---|
| PHP 8.3 + Laravel 10 | Backend API |
| Laravel Sanctum | Authentification par jeton |
| MySQL | Base de données |

---

## 🏗️ Architecture

L'application suit le pattern **MVVM (Model-View-ViewModel)** couplé au pattern **Repository**, ce qui sépare clairement :

```
app/
├── data/
│   ├── api/          → ApiService (Retrofit), ApiClient, AuthInterceptor
│   ├── model/        → Classes de données (Eleve, Note, Paiement, Absence...)
│   ├── repository/   → ApiRepository (point d'accès unique aux données)
│   └── SessionManager.kt  → Gestion du jeton et de l'enfant sélectionné
├── ui/
│   ├── login/        → Écran de connexion
│   ├── main/         → Activité principale + sélecteur d'enfant
│   ├── dashboard/    → Tableau de bord de l'élève
│   ├── notes/        → Consultation des notes par matière
│   ├── paiements/    → Suivi des paiements + téléchargement des reçus
│   ├── absences/     → Liste des absences
│   ├── annonces/     → Annonces de l'école
│   └── settings/     → Changement de mot de passe
└── util/
    ├── ApiResult.kt       → Résultat uniforme des appels API
    ├── FileDownloader.kt  → Téléchargement et ouverture des reçus PDF
    └── ViewModelFactory.kt → Injection des dépendances (ServiceLocator)
```

---

## ✅ Fonctionnalités réalisées

### 🔐 Authentification Parent
- [x] Connexion sécurisée par email et mot de passe (jeton Sanctum)
- [x] Déconnexion (révocation du jeton)
- [x] Modification du mot de passe

### 📊 Tableau de bord de l'élève
- [x] Affichage des informations de base (nom, prénom, photo, classe)
- [x] Affichage de la moyenne générale
- [x] Affichage du rang dans la classe
- [x] Résumé des dernières notes obtenues
- [x] Sélecteur d'enfant si le parent en a plusieurs

### 📝 Consultation des notes
- [x] Liste des matières avec coefficient
- [x] Affichage des notes par matière
- [x] Navigation entre les 3 trimestres
- [x] Calcul et affichage des moyennes trimestrielles

### 💳 Suivi des paiements
- [x] Récapitulatif : frais total, montant payé, reste à payer
- [x] Historique des versements effectués
- [x] Téléchargement du reçu de paiement (PDF)

### 🚫 Suivi des absences
- [x] Liste des absences avec date
- [x] Statut de justification (Justifiée / Non justifiée)
- [x] Motif d'absence si renseigné

### 📢 Notifications et annonces
- [x] Réception des annonces de l'école
- [x] Badge « Non lue » sur les annonces non consultées
- [x] Types d'annonces : réunion, examen, paiement, info

---

## ⚙️ Installation et configuration

### Prérequis
- Android Studio Hedgehog (2023.1.1) ou version ultérieure
- JDK 8 ou supérieur
- Le projet backend Laravel doit être en cours d'exécution (voir dépôt web)

### Étapes d'installation

**1. Cloner le dépôt**
```bash
git clone https://github.com/johnthierrysandiwidi-bot/projet_school_connect.git
cd projet_school_connect/suivi-scolaire-mobile
```

**2. Ouvrir dans Android Studio**
```
File → Open → sélectionner le dossier suivi-scolaire-mobile
```

**3. Configurer l'URL de l'API**

Ouvrir `app/build.gradle.kts` et modifier la variable `API_BASE_URL` selon ton environnement :

```kotlin
// Émulateur Android (serveur Laravel sur le même PC)
buildConfigField("String", "API_BASE_URL", "\"http://10.0.2.2:8000/api/\"")

// Téléphone physique sur le même réseau Wi-Fi
buildConfigField("String", "API_BASE_URL", "\"http://192.168.X.X:8000/api/\"")

// Serveur en ligne
buildConfigField("String", "API_BASE_URL", "\"https://ton-domaine.com/api/\"")
```

**4. Synchroniser Gradle**
```
File → Sync Project with Gradle Files
```

**5. Lancer l'application**
- Brancher un appareil Android (API 26+) ou démarrer un émulateur
- Cliquer sur ▶️ **Run** dans Android Studio

---

## 🚀 Lancement du backend Laravel

Avant de lancer l'application mobile, le serveur Laravel doit être démarré :

```bash
cd suivi-scolaire
composer install
php artisan migrate:fresh --seed
php artisan db:seed --class=ElevesReelsSeeder
php artisan storage:link
php artisan serve
```

### Compte de connexion (application mobile)
| Rôle | Email | Mot de passe |
|---|---|---|
| Parent (démo) | parent@ecole.bf | parent123 |
| Parent (généré) | parent1@ecole.bf … parent25@ecole.bf | parent123 |

---

## 🔌 API REST consommée

L'application consomme les routes suivantes (protégées par jeton Sanctum) :

| Méthode | Route | Description |
|---|---|---|
| POST | `/api/login` | Connexion et récupération du jeton |
| POST | `/api/logout` | Déconnexion |
| PUT | `/api/password` | Changement de mot de passe |
| GET | `/api/enfants` | Liste des enfants du parent connecté |
| GET | `/api/enfants/{id}` | Tableau de bord d'un enfant |
| GET | `/api/enfants/{id}/notes` | Notes par matière et par trimestre |
| GET | `/api/enfants/{id}/paiements` | Historique des paiements |
| GET | `/api/enfants/{id}/paiements/{id}/recu` | Téléchargement du reçu PDF |
| GET | `/api/enfants/{id}/absences` | Liste des absences |
| GET | `/api/annonces` | Annonces de l'école |
| POST | `/api/annonces/{id}/lue` | Marquer une annonce comme lue |

---

## 📁 Structure du dépôt GitHub

```
projet_school_connect/
├── suivi-scolaire/          → Projet web Laravel (backend + API REST)
│   ├── app/                 → Contrôleurs, Modèles, Resources API
│   ├── database/            → Migrations et Seeders
│   ├── routes/api.php       → Routes de l'API REST
│   └── README.md            → Documentation du projet web
│
└── suivi-scolaire-mobile/   → Application mobile Android (Kotlin)
    ├── app/src/main/java/   → Code source Kotlin
    ├── app/src/main/res/    → Layouts XML, ressources
    └── README.md            → Ce fichier
```

---

## 📜 Licence

Projet académique — Université Joseph Ki-Zerbo, UFR/SEA — Année 2025-2026.
