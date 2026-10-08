<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.users.course_year_confirmed_at: quando l'utente ha confermato per l'ultima volta il suo
 * anno di corso. Ogni ottobre viene chiesto di confermarlo (documento HR 2.2.1): finché la data
 * è precedente all'inizio dell'anno accademico in corso, al login compare la richiesta.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('core.users', function (Blueprint $table) {
            $table->timestampTz('course_year_confirmed_at')->nullable()->after('course_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('core.users', function (Blueprint $table) {
            $table->dropColumn('course_year_confirmed_at');
        });
    }
};
