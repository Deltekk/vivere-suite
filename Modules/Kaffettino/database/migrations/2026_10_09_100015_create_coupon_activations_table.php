<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.coupon_activations: coupon attivati dal portale per il prossimo acquisto.
 * Ogni coupon si usa una sola volta per persona: la chiave primaria (coupon_id, user_id)
 * impedisce una seconda attivazione.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.coupon_activations', function (Blueprint $table) {
            // ----- Data -----
            $table->timestampTz('activated_at')->useCurrent();
            $table->timestampTz('used_at')->nullable(); // Valorizzato al primo acquisto in cui viene applicato

            // ----- Foreign Keys -----
            $table->foreignUuid('coupon_id')->constrained('kaffettino.coupons');
            $table->foreignUuid('user_id')->index()->constrained('core.users');
            $table->foreignUuid('transaction_id')->nullable()->constrained('kaffettino.transactions'); // Movimento in cui è stato usato

            // ----- Primary keys -----
            $table->primary(['coupon_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.coupon_activations');
    }
};
