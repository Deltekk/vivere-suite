<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.classroom_course: aule in cui si tengono le lezioni di un corso (N:N).
 * Un corso può usare più aule, anche in edifici diversi. Serve per esempio a Oggetti Smarriti
 * per capire a quali corsi segnalare un oggetto trovato in un'aula.
 *
 * Tabella pivot: chiave primaria composta, nessun id surrogato.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.classroom_course', function (Blueprint $table) {
            // ----- Foreign Keys -----
            $table->foreignUuid('classroom_id')->constrained('core.classrooms')->cascadeOnDelete();
            $table->foreignUuid('course_id')->index()->constrained('core.courses')->cascadeOnDelete();

            // ----- Timestamps -----
            $table->timestampsTz();

            // ----- Primary keys -----
            $table->primary(['classroom_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.classroom_course');
    }
};
