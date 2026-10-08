<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * core.prevent_changes(): funzione trigger che rende una tabella append-only a livello di DB.
 *
 * Si collega a una tabella con:
 *   CREATE TRIGGER <nome> BEFORE UPDATE OR DELETE ON <schema.tabella>
 *       FOR EACH ROW EXECUTE FUNCTION core.prevent_changes();
 *
 * Così nemmeno un bug o una query scritta a mano possono modificare o cancellare le righe
 * (es. i movimenti di denaro di Kaffettino). Le correzioni si fanno con nuove righe.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION core.prevent_changes() RETURNS trigger
            LANGUAGE plpgsql AS $$
            BEGIN
                RAISE EXCEPTION 'La tabella %.% è append-only: le righe non si modificano né si cancellano', TG_TABLE_SCHEMA, TG_TABLE_NAME;
            END;
            $$
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP FUNCTION IF EXISTS core.prevent_changes()');
    }
};
