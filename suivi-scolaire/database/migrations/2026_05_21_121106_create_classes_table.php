<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('niveau', ['CP1','CP2','CE1','CE2','CM1','CM2']);
            $table->decimal('frais_scolarite', 10, 2)->default(0);
            $table->string('annee_scolaire');
            $table->integer('capacite_max')->default(40);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};