<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('cascade');
            $table->decimal('valeur', 5, 2);
            $table->enum('trimestre', ['1', '2', '3']);
            $table->string('annee_scolaire');
            $table->foreignId('user_id')->constrained('users');
            $table->text('appreciation')->nullable();
            $table->timestamps();

            $table->unique(['eleve_id', 'matiere_id', 'trimestre', 'annee_scolaire']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};