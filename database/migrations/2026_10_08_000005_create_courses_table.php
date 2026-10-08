<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.courses: corsi di studio dell'ateneo.
 * La macroarea si ricava dal dipartimento (courses -> departments -> macroareas).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.courses', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('name')->unique();

            // ----- Foreign Keys -----
            $table->foreignUuid('department_id')->index()->constrained('core.departments');

            // Auletta di afferenza del corso: decide chi vede i resoconti Kaffettino di quell'auletta
            // e dove vengono gestiti gli oggetti smarriti. NULL se il corso non ha un'auletta.
            $table->foreignUuid('auletta_id')->nullable()->index()->constrained('core.aulette');

            // ----- Timestamps -----
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.courses');
    }
};
