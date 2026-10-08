<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.departments: dipartimenti accademici (es. "Dipartimento di Ingegneria").
 * Sono unità organizzative, non luoghi fisici: per gli edifici vedi core.buildings.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.departments', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('name')->unique();

            // ----- Foreign Keys -----
            $table->foreignUuid('macroarea_id')->index()->constrained('core.macroareas');

            // ----- Timestamps -----
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.departments');
    }
};
