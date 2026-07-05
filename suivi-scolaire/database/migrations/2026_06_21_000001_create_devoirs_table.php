<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->comment("L'enseignant qui a créé le devoir");
            $table->string('titre');
            $table->text('description')->nullable();
            $table->date('date_devoir');
            $table->date('date_limite')->nullable();
            $table->enum('trimestre', ['1', '2', '3']);
            $table->string('annee_scolaire');
            $table->boolean('noter')->default(true)->comment('Devoir noté ou simple consigne sans note');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devoirs');
    }
};
