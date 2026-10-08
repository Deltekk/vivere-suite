<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.aulette: le aulette dell'associazione.
 * "Auletta" è un termine di dominio: eccezione documentata alla convenzione dei nomi in inglese.
 * Le impostazioni specifiche di un modulo (es. il magazzino Kaffettino) stanno nello schema del modulo.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.aulette', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('name')->unique();

            // Coordinate per mostrare le aulette su una mappa, facoltative
            $table->double('lat')->nullable();
            $table->double('lon')->nullable();

            // ----- Foreign Keys -----
            $table->foreignUuid('building_id')->index()->constrained('core.buildings'); // Edificio in cui si trova l'auletta

            // ----- Timestamps -----
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.aulette');
    }
};
