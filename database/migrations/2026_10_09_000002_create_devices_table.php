<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.devices: dispositivi embedded della suite (gestione fleet).
 *
 * Sono nel core perché li usano più moduli: oggi gli ESP32 di Kaffettino, in futuro i kiosk
 * di Calendario e i lettori del Magazzino. Si autenticano all'API con un token proprio.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.devices', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('name');
            $table->enum('type', ['KaffettinoReader']); // App\Enums\DeviceType
            $table->string('token_hash', 64)->unique(); // SHA-256 del token API (il token in chiaro si mostra una volta sola)

            // Codice della modalità amministratore (cambio wifi): CIFRATO e non hashato, perché quando
            // il dispositivo è offline l'admin deve poterlo rileggere dal pannello. Al dispositivo
            // arriva solo un hash con sale, con cui lo verifica anche senza rete.
            $table->text('admin_code')->nullable();
            $table->timestampTz('admin_code_changed_at')->nullable();
            $table->timestampTz('config_synced_at')->nullable(); // Se precedente a admin_code_changed_at il nuovo codice non è ancora arrivato

            $table->text('firmware_version')->nullable();
            $table->timestampTz('last_seen_at')->nullable();
            $table->text('last_ip')->nullable();
            $table->timestampTz('revoked_at')->nullable();

            // ----- Foreign Keys -----
            $table->foreignUuid('auletta_id')->index()->constrained('core.aulette'); // Modificabile: il dispositivo si può spostare

            // ----- Timestamps -----
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.devices');
    }
};
