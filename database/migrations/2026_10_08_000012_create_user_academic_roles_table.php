<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * core.user_academic_roles: mandati istituzionali (chi ha ricoperto quale carica e quando).
 * Ogni riga è un mandato: lo storico si conserva perché un mandato scaduto e non rinnovato
 * resta archiviato. Una persona può avere più cariche contemporaneamente.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.user_academic_roles', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->date('started_at');
            $table->date('expires_at')->index(); // Data di decadenza: indicizzata per il job che archivia i mandati scaduti

            // ----- Foreign Keys -----
            $table->foreignUuid('user_id')->constrained('core.users');
            $table->foreignUuid('academic_role_id')->index()->constrained('core.academic_roles');

            // ----- Timestamps -----
            $table->timestampsTz();

            // ----- Indexes -----
            // Nome esplicito: quello generato supererebbe i 63 caratteri ammessi da Postgres e verrebbe troncato
            $table->unique(['user_id', 'academic_role_id', 'started_at', 'expires_at'], 'core_user_academic_roles_mandate_unique');
        });

        DB::statement('ALTER TABLE core.user_academic_roles ADD CONSTRAINT core_user_academic_roles_dates_check CHECK (expires_at > started_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.user_academic_roles');
    }
};
