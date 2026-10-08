<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.changelog_user: chi ha già visto quale changelog. Se la riga esiste, l'ha visto.
 *
 * Tabella pivot: chiave primaria composta, quindi ogni utente vede una voce una volta sola.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.changelog_user', function (Blueprint $table) {
            // ----- Data -----
            $table->timestampTz('seen_at')->useCurrent();

            // ----- Foreign Keys -----
            $table->foreignUuid('changelog_id')->constrained('core.changelogs')->cascadeOnDelete();
            $table->foreignUuid('user_id')->index()->constrained('core.users')->cascadeOnDelete();

            // ----- Primary keys -----
            $table->primary(['changelog_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.changelog_user');
    }
};
