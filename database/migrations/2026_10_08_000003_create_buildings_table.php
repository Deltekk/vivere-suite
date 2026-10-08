<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.buildings: edifici fisici dell'ateneo (es. "Edificio 8").
 * Un edificio può ospitare aule di dipartimenti diversi, per questo è separato da core.departments.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.buildings', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('name');
            $table->integer('number')->nullable()->unique(); // Numero dell'edificio (es. 8); NULL per le sedi senza numero

            // Coordinate per le mappe (Leaflet), facoltative
            $table->double('lat')->nullable();
            $table->double('lon')->nullable();

            // ----- Timestamps -----
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.buildings');
    }
};
