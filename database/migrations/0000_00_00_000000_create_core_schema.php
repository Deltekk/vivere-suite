<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * Crea lo schema Postgres "core", che contiene le entità condivise da tutti i moduli
 * (utenti, corsi, aulette, notifiche, ...).
 *
 * Convenzione degli schemi:
 *  - public     -> tabelle tecniche di Laravel (migrations, cache, jobs, sessions, ...)
 *  - core       -> entità usate da più moduli
 *  - <modulo>   -> tabelle usate da un solo modulo (es. kaffettino), create dalle migration del modulo
 *
 * Il nome del file inizia con 0000_ perché deve girare prima di qualsiasi altra migration.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS core');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Senza CASCADE: se lo schema contiene ancora tabelle il rollback si ferma invece di cancellarle
        DB::statement('DROP SCHEMA IF EXISTS core');
    }
};
