<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.birthday_gifts: caffè gratis del compleanno, uno all'anno per persona in qualunque magazzino.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.birthday_gifts', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->foreignUuid('transaction_id')->primary()->constrained('kaffettino.transactions'); // Movimento BirthdayGift (1:1)

            // ----- Data -----
            $table->smallInteger('year');

            // ----- Foreign Keys -----
            $table->foreignUuid('user_id')->constrained('core.users');

            // ----- Indexes -----
            $table->unique(['user_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.birthday_gifts');
    }
};
