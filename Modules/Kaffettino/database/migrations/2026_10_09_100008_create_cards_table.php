<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.cards: card NFC. Una sola card attiva per persona, valida per tutti i suoi conti.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.cards', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('uid')->unique();                              // Identificativo NFC letto dal PN532
            $table->string('pin_hash')->nullable();                     // PIN facoltativo (D9): solo hash, si reimposta via mail
            $table->smallInteger('pin_failed_attempts')->default(0);    // Blocco dopo troppi tentativi (i PIN sono corti)
            $table->timestampTz('assigned_at');
            $table->timestampTz('revoked_at')->nullable();              // Card smarrita o sostituita

            // ----- Foreign Keys -----
            $table->foreignUuid('user_id')->constrained('core.users');
            $table->foreignUuid('assigned_by')->constrained('core.users'); // Admin che ha assegnato la card

            // ----- Timestamps -----
            $table->timestampsTz();
        });

        DB::statement('CREATE UNIQUE INDEX kaffettino_cards_active_user_unique ON kaffettino.cards (user_id) WHERE revoked_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.cards');
    }
};
