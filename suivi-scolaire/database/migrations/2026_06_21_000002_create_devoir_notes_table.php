<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoir_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devoir_id')->constrained('devoirs')->onDelete('cascade');
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->decimal('valeur', 5, 2)->nullable();
            $table->text('remarque')->nullable();
            $table->timestamps();

            $table->unique(['devoir_id', 'eleve_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devoir_notes');
    }
};
