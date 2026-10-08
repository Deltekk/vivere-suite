<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.academic_roles: cariche istituzionali che un rappresentante eletto può ricoprire
 * (seed iniziale in AcademicRoleSeeder: CDD, CCS, CDA, CDA-ERSU, CNSU, CSU).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.academic_roles', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            // Non è un enum perché le cariche devono essere personalizzabili (può nascerne una nuova)
            $table->text('role')->unique();

            // ----- Timestamps -----
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.academic_roles');
    }
};
