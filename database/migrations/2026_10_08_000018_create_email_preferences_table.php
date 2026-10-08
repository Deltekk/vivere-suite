<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.email_preferences: per quali servizi l'utente vuole ricevere le email.
 * Si sceglie in registrazione e si può cambiare in qualsiasi momento dal profilo.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.email_preferences', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('service');                  // Alias del modulo: humanresources, kaffettino, drive, ...
            $table->boolean('enabled')->default(true);

            // ----- Foreign Keys -----
            $table->foreignUuid('user_id')->constrained('core.users')->cascadeOnDelete();

            // ----- Timestamps -----
            $table->timestampsTz(); // Tracciano quando è cambiata la scelta (accountability del consenso)

            // ----- Indexes -----
            $table->unique(['user_id', 'service']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.email_preferences');
    }
};
