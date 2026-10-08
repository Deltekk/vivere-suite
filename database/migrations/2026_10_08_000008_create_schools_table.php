<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.schools: scuole superiori di provenienza.
 * Indicarla è facoltativo per gli studenti e obbligatorio per lo staff (piattaforma Orientamento).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.schools', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('name');
            $table->text('city');

            // Coordinate facoltative per la mappa: chi si registra conosce il nome della scuola, non la posizione.
            // Bastano due double, senza PostGIS: l'unico uso previsto è mostrare un punto su Leaflet.
            $table->double('lat')->nullable();
            $table->double('lon')->nullable();

            // ----- Timestamps -----
            $table->timestampsTz();

            // ----- Indexes -----
            $table->unique(['name', 'city']); // Possono esistere scuole con lo stesso nome in città diverse
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.schools');
    }
};
