<?php

namespace App\Providers;

use App\Models\Parametre;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('layouts.partials.pagination');

        $this->surchargerAnneeScolaireActive();
        $this->surchargerNomEcole();
        $this->surchargerCoordonneesEcole();
    }

    /**
     * L'année scolaire courante vient normalement de la variable d'env.
     * ANNEE_SCOLAIRE (voir config/app.php). Si le Gestionnaire l'a changée
     * depuis la page "Paramètres" de l'application, la valeur enregistrée
     * en base de données prend le dessus — sans avoir besoin de modifier
     * .env ni de relanger le serveur.
     */
    private function surchargerAnneeScolaireActive(): void
    {
        try {
            if (! Schema::hasTable('parametres')) {
                return;
            }

            $valeur = Parametre::lire('annee_scolaire_active');

            if ($valeur) {
                config(['app.annee_scolaire' => $valeur]);
            }
        } catch (\Throwable $e) {
            // Base de données pas encore migrée (ex: pendant `php artisan migrate`
            // lui-même) : on continue avec la valeur de .env, sans planter.
        }
    }

    /**
     * Même principe que ci-dessus pour le nom de l'établissement (affiché
     * sur les bulletins, reçus et l'en-tête de l'application) : modifiable
     * depuis la page Paramètres, sans toucher à .env ni au code.
     */
    private function surchargerNomEcole(): void
    {
        try {
            if (! Schema::hasTable('parametres')) {
                return;
            }

            $valeur = Parametre::lire('nom_ecole');

            if ($valeur) {
                config(['app.nom_ecole' => $valeur]);
            }
        } catch (\Throwable $e) {
            // Base de données pas encore migrée : on garde la valeur de .env.
        }
    }

    /**
     * Adresse et téléphone de l'établissement (optionnels), affichés dans
     * l'en-tête des bulletins et reçus PDF s'ils sont renseignés.
     */
    private function surchargerCoordonneesEcole(): void
    {
        try {
            if (! Schema::hasTable('parametres')) {
                return;
            }

            $adresse = Parametre::lire('adresse_ecole');
            if ($adresse !== null) {
                config(['app.adresse_ecole' => $adresse]);
            }

            $telephone = Parametre::lire('telephone_ecole');
            if ($telephone !== null) {
                config(['app.telephone_ecole' => $telephone]);
            }
        } catch (\Throwable $e) {
            // Base de données pas encore migrée : on garde la valeur de .env.
        }
    }
}
