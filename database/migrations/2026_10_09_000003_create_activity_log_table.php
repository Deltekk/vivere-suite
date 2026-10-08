<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * core.activity_log: audit log di tutta la suite (spatie/laravel-activitylog, model App\Models\Activity).
 *
 * Requisiti del preambolo: append-only, non modificabile, leggibile solo dai super admin,
 * conservato 2 anni (D13). Per questo un trigger rifiuta qualsiasi UPDATE e permette il DELETE
 * solo delle righe più vecchie del periodo di conservazione (le cancella "activitylog:clean").
 * Gli stessi eventi finiscono anche su file (canale di log "audit", storage/logs/audit-*.log).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.activity_log', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('log_name')->nullable()->index();   // Modulo che ha generato l'evento (humanresources, kaffettino, ...)
            $table->text('description');
            $table->text('event')->nullable();               // created, updated, deleted o evento applicativo (es. banned)
            $table->jsonb('attribute_changes')->nullable();  // Valori prima/dopo
            $table->jsonb('properties')->nullable();         // Dati aggiuntivi (es. motivazione del ban)

            // ----- Foreign Keys -----
            $table->nullableUuidMorphs('subject', 'core_activity_log_subject_index'); // Su cosa (alias del morph map)
            $table->nullableUuidMorphs('causer', 'core_activity_log_causer_index');   // Chi (di norma un utente)

            // ----- Timestamps -----
            $table->timestampsTz();

            // ----- Indexes -----
            $table->index('created_at');
        });

        // Il periodo di conservazione è fissato al momento della migration: se cambia
        // VIVERE_AUDIT_RETENTION_DAYS serve una nuova migration che ricrei la funzione.
        $retentionDays = (int) config('vivere.audit_retention_days');

        DB::statement(<<<SQL
            CREATE OR REPLACE FUNCTION core.protect_activity_log() RETURNS trigger
            LANGUAGE plpgsql AS \$\$
            BEGIN
                IF TG_OP = 'DELETE' AND OLD.created_at < now() - make_interval(days => {$retentionDays}) THEN
                    RETURN OLD;
                END IF;
                RAISE EXCEPTION 'core.activity_log è append-only: si possono cancellare solo le righe più vecchie di {$retentionDays} giorni';
            END;
            \$\$
            SQL);
        DB::statement('CREATE TRIGGER core_activity_log_append_only BEFORE UPDATE OR DELETE ON core.activity_log FOR EACH ROW EXECUTE FUNCTION core.protect_activity_log()');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.activity_log');
        DB::statement('DROP FUNCTION IF EXISTS core.protect_activity_log()');
    }
};
