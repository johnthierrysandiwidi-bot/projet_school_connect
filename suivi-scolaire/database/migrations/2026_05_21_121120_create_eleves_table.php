<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eleves', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('lieu_naissance')->nullable();
            $table->enum('sexe', ['M', 'F']);
            $table->string('nationalite')->default('Burkinabè');
            $table->string('photo')->nullable();
            $table->foreignId('classe_id')->constrained('classes')->onDelete('restrict');
            $table->string('annee_scolaire');
            $table->enum('statut', ['actif','redoublant','transfere','exclu'])->default('actif');
            $table->string('parent_nom');
            $table->string('parent_prenom');
            $table->string('parent_telephone');
            $table->string('parent_telephone2')->nullable();
            $table->string('parent_adresse')->nullable();
            $table->enum('parent_lien', ['père','mère','tuteur','autre'])->default('père');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eleves');
    }
};