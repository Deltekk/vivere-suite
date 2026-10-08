<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * Schema Postgres "kaffettino": tabelle usate solo dal modulo Kaffettino.
 * Le entità condivise (utenti, aulette, dispositivi) restano in "core" e si referenziano con FK reali.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS kaffettino');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SCHEMA IF EXISTS kaffettino');
    }
};
