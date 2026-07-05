<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annonce_lectures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annonce_id')->constrained('annonces')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('lu_at');

            $table->unique(['annonce_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annonce_lectures');
    }
};
