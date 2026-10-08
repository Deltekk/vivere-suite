<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.bans: storico dei ban, mai cancellato (solo created_at, nessun update).
 *
 * email_pattern serve a riconoscere chi, dopo un ban, si registra di nuovo con la seconda
 * mail istituzionale: il match non rifiuta la registrazione, la lascia in attesa (Pending)
 * e mostra un warning agli staffer (rischio omonimi).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.bans', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('reason');                  // Motivazione obbligatoria: viene inviata per mail all'utente
            $table->text('email_pattern')->index();  // Parte locale normalizzata della mail, es. "marioluigi.rossi"

            // ----- Foreign Keys -----
            $table->foreignUuid('user_id')->index()->constrained('core.users');   // Utente bannato
            $table->foreignUuid('banned_by')->constrained('core.users');          // Admin che ha eseguito il ban

            // ----- Timestamps -----
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.bans');
    }
};
