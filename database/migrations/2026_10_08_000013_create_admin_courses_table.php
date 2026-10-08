<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.admin_courses: amministratori di ogni corso (N:N).
 * Una persona può amministrare anche corsi diversi dal proprio, così nessun corso
 * resta senza staff.
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
        Schema::create('core.admin_courses', function (Blueprint $table) {
            // ----- Foreign Keys -----
            $table->foreignUuid('user_id')->constrained('core.users')->cascadeOnDelete();
            $table->foreignUuid('course_id')->index()->constrained('core.courses')->cascadeOnDelete();

            // ----- Timestamps -----
            $table->timestampTz('created_at')->useCurrent();

            // ----- Primary keys -----
            $table->primary(['user_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.admin_courses');
    }
};
