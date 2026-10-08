<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.staff_profiles: dati richiesti solo a staff e amministratori (relazione 1:1 con core.users).
 * Sono tenuti separati da core.users per minimizzazione GDPR: gli studenti non li forniscono.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.staff_profiles', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('tax_code')->unique(); // Codice fiscale
            $table->text('birth_city');
            $table->text('birth_province');
            $table->text('birth_country');

            // ----- Foreign Keys -----
            $table->foreignUuid('user_id')->unique()->constrained('core.users')->cascadeOnDelete();

            // ----- Timestamps -----
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.staff_profiles');
    }
};
