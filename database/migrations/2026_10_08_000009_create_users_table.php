<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * core.users: dati comuni a studenti e staff. Esiste un solo User per tutta la suite.
 *
 * Gli utenti non si cancellano fisicamente: chi si disiscrive viene anonimizzato (anonymized_at).
 * I valori degli enum sono scritti qui a mano perché una migration è una fotografia del passato:
 * non deve cambiare se in futuro cambiano gli enum PHP (App\Enums\*).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.users', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('name');
            $table->text('surname');
            $table->text('username');             // Formato Nome.Cognome richiesto in registrazione (es. MarioLuigi.Rossi03), poi modificabile
            $table->date('birthday');             // Data completa: serve per il controllo età >= 16 e per il caffè del compleanno
            $table->enum('course_year', [         // App\Enums\CourseYear
                'S4', 'S5', 'T1', 'T2', 'T3', 'TFC', 'M1', 'M2', 'MFC', 'Laureando',
                'PTT-1-1', 'PTT-1-2', 'PTT-2-1', 'PTT-2-2', 'PTT-3-1', 'PTT-3-2',
                'PTM-1-1', 'PTM-1-2', 'PTM-2-1', 'PTM-2-2',
            ]);
            $table->text('phone_number');         // Obbligatorio: serve allo staff per contattare chi ha perso un oggetto (Oggetti Smarriti)
            $table->text('email')->unique();      // Salvata sempre in minuscolo (vedi User). Formato UNIPA tranne che per le superiori (S4, S5)
            $table->text('telegram_tag')->nullable();
            $table->text('instagram_tag')->nullable();
            $table->string('password');           // Hash bcrypt gestito da Laravel (cast "hashed" nel model)
            $table->rememberToken();
            $table->enum('role', ['Student', 'Staff', 'Admin', 'SuperAdmin'])->default('Student');           // App\Enums\Role
            $table->enum('status', ['Pending', 'Active', 'Banned', 'To_Confirm'])->default('Pending')->index(); // App\Enums\UserStatus
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable(); // App\Enums\Gender: facoltativo, serve solo a declinare i testi
            $table->timestampTz('email_verified_at')->nullable();

            // Privacy (accountability GDPR)
            $table->timestampTz('privacy_accepted_at'); // Quando ha accettato l'informativa in registrazione
            $table->text('terms_version');              // Versione dell'informativa accettata: se cambia, va richiesta di nuovo
            $table->timestampTz('anonymized_at')->nullable(); // Valorizzato quando l'utente si disiscrive e i suoi dati vengono anonimizzati

            // ----- Foreign Keys -----
            $table->foreignUuid('course_id')->nullable()->index()->constrained('core.courses'); // NULL per gli studenti delle superiori
            $table->foreignUuid('school_id')->nullable()->index()->constrained('core.schools'); // Obbligatoria per lo staff (regola applicativa)

            // ----- Timestamps -----
            $table->timestampsTz();
        });

        // Username e tag social devono essere unici senza distinguere maiuscole/minuscole
        // ("mario.rossi" e "Mario.Rossi" sono la stessa persona). Laravel non ha un helper
        // per gli indici unique su espressioni, quindi li creiamo in SQL.
        DB::statement('CREATE UNIQUE INDEX core_users_username_unique ON core.users (lower(username))');
        DB::statement('CREATE UNIQUE INDEX core_users_telegram_tag_unique ON core.users (lower(telegram_tag))');
        DB::statement('CREATE UNIQUE INDEX core_users_instagram_tag_unique ON core.users (lower(instagram_tag))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.users');
    }
};
