<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.notifications: notifiche in piattaforma, condivise da tutti i moduli.
 *
 * È la tabella standard del canale "database" di Laravel (usata anche dalla campanella
 * di Filament), spostata nello schema core. Il model è App\Models\Notification.
 * Titolo e testo stanno in "data" (jsonb): deve essere jsonb e non text, perché
 * Filament filtra con data->format.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.notifications', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('type');         // Classe della Notification Laravel che l'ha generata
            $table->jsonb('data');        // Contenuto (titolo, testo, azioni)
            $table->timestampTz('read_at')->nullable();

            // ----- Foreign Keys -----
            $table->uuidMorphs('notifiable'); // Destinatario (nella pratica sempre un User, alias "user")

            // ----- Timestamps -----
            $table->timestampsTz();

            // ----- Indexes -----
            $table->index(['notifiable_id', 'read_at']); // "Le mie notifiche non lette"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.notifications');
    }
};
