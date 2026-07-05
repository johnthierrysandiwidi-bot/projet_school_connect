<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Au primaire (Burkina Faso), toutes les matières ne sont pas notées sur
 * la même échelle : généralement sur 10 du CP1 au CE2, et un mélange de
 * matières sur 10 et sur 20 au CM1/CM2. `bareme` indique sur combien de
 * points est notée chaque matière (10 ou 20) ; la moyenne trimestrielle,
 * elle, est toujours recalculée et affichée sur 10 (voir MoyenneService).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matieres', function (Blueprint $table) {
            $table->unsignedTinyInteger('bareme')->default(20)->after('coefficient');
        });
    }

    public function down(): void
    {
        Schema::table('matieres', function (Blueprint $table) {
            $table->dropColumn('bareme');
        });
    }
};
