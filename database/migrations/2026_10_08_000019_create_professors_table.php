<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.professors: professori dell'ateneo, popolata da un job di scraping schedulato e isolato
 * (se fallisce non blocca nulla). Il confronto con chi si registra si fa sul nome normalizzato
 * e produce un warning per gli staffer, mai un blocco automatico.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.professors', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('full_name');
            $table->text('normalized_name')->index(); // Minuscolo, senza spazi né accenti: usato per il confronto
            $table->timestampTz('scraped_at');        // Ultimo scraping in cui il professore è stato trovato
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.professors');
    }
};
