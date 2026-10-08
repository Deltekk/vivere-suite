<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.user_settings: impostazioni Kaffettino per persona (non per conto).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.user_settings', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->foreignUuid('user_id')->primary()->constrained('core.users')->cascadeOnDelete();

            // ----- Data -----
            // Consenso alle statistiche anonime ("bevi più caffè del 60% degli studenti"): NULL = non partecipa
            $table->timestampTz('statistics_consent_at')->nullable();

            // ----- Timestamps -----
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.user_settings');
    }
};
