<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eleves', function (Blueprint $table) {
            $table->foreignId('eleve_origine_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('eleves')
                  ->nullOnDelete()
                  ->comment("Référence le dossier de l'élève pour l'année scolaire précédente, lors d'un passage en classe supérieure ou d'un redoublement.");
        });
    }

    public function down(): void
    {
        Schema::table('eleves', function (Blueprint $table) {
            $table->dropConstrainedForeignId('eleve_origine_id');
        });
    }
};
