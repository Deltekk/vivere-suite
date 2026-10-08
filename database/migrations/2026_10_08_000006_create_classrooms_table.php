<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.classrooms: aule dell'ateneo (es. codice "F220", nome "Aula Rubino").
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.classrooms', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('name')->nullable(); // Nome dell'aula, se esiste (es. "Aula Rubino")
            $table->text('code');             // Codice dell'aula (es. "F220")

            // ----- Foreign Keys -----
            $table->foreignUuid('building_id')->index()->constrained('core.buildings');                   // Edificio in cui si trova
            $table->foreignUuid('department_id')->nullable()->index()->constrained('core.departments');   // Dipartimento che la gestisce, se noto

            // ----- Timestamps -----
            $table->timestampsTz();

            // ----- Indexes -----
            $table->unique(['building_id', 'code']); // Lo stesso codice può ripetersi solo in edifici diversi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.classrooms');
    }
};
