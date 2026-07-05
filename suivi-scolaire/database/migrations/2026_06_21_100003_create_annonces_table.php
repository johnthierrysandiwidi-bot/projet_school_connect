<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annonces', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('contenu');
            $table->enum('type', ['info', 'examen', 'reunion', 'paiement'])->default('info');
            // Si classe_id est nul, l'annonce concerne toute l'école.
            $table->foreignId('classe_id')->nullable()->constrained('classes')->onDelete('cascade');
            $table->date('date_publication');
            $table->foreignId('user_id')->constrained('users')->comment('Publiée par');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};
