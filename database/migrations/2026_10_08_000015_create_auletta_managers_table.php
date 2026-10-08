<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * core.auletta_managers: gestori delle aulette (N:N con storico).
 * Possono esserlo solo utenti non studenti (regola applicativa). Alla nomina e alla rimozione
 * parte la mail per ritirare o restituire le chiavi (keys_state, App\Enums\KeyState).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.auletta_managers', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->enum('keys_state', ['Handing_Over', 'Handed_Over', 'Requested', 'Returned'])->default('Handing_Over'); // App\Enums\KeyState
            $table->timestampTz('assigned_at');
            $table->timestampTz('removed_at')->nullable(); // NULL = gestione attiva

            // ----- Foreign Keys -----
            $table->foreignUuid('user_id')->constrained('core.users');
            $table->foreignUuid('auletta_id')->index()->constrained('core.aulette');

            // ----- Timestamps -----
            $table->timestampsTz();

            // ----- Indexes -----
            $table->unique(['user_id', 'auletta_id', 'assigned_at']);
        });

        // Al massimo una gestione attiva per coppia utente/auletta
        DB::statement('CREATE UNIQUE INDEX core_auletta_managers_active_unique ON core.auletta_managers (user_id, auletta_id) WHERE removed_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.auletta_managers');
    }
};
