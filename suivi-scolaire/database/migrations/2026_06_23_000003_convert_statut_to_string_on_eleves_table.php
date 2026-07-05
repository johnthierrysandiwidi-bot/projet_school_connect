<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La colonne `statut` était un ENUM strict ('actif','redoublant','transfere',
 * 'exclu'), ce qui demande une migration chaque fois qu'on veut ajouter une
 * valeur (ici : 'diplome', pour un élève ayant terminé le cycle primaire).
 * On la convertit en simple chaîne — les valeurs autorisées restent
 * contrôlées par la validation (StoreEleveRequest/UpdateEleveRequest), pas
 * par une contrainte de base de données.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eleves', function (Blueprint $table) {
            $table->string('statut_tmp')->default('actif')->after('statut');
        });

        DB::statement('UPDATE eleves SET statut_tmp = statut');

        Schema::table('eleves', function (Blueprint $table) {
            $table->dropColumn('statut');
        });

        Schema::table('eleves', function (Blueprint $table) {
            $table->renameColumn('statut_tmp', 'statut');
        });
    }

    public function down(): void
    {
        Schema::table('eleves', function (Blueprint $table) {
            $table->enum('statut_tmp', ['actif', 'redoublant', 'transfere', 'exclu'])->default('actif')->after('statut');
        });

        DB::statement("UPDATE eleves SET statut_tmp = statut WHERE statut IN ('actif','redoublant','transfere','exclu')");

        Schema::table('eleves', function (Blueprint $table) {
            $table->dropColumn('statut');
        });

        Schema::table('eleves', function (Blueprint $table) {
            $table->renameColumn('statut_tmp', 'statut');
        });
    }
};
