<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.coupons: sconti percentuali, non cumulabili, validi solo sui prodotti scelti
 * (kaffettino.coupon_product) e con scadenza obbligatoria.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.coupons', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('code')->unique();
            $table->smallInteger('discount_percent');
            $table->timestampTz('starts_at');
            $table->timestampTz('expires_at');

            // ----- Foreign Keys -----
            $table->foreignUuid('created_by')->constrained('core.users');

            // ----- Timestamps -----
            $table->timestampsTz();
        });

        DB::statement('ALTER TABLE kaffettino.coupons ADD CONSTRAINT kaffettino_coupons_values_check CHECK (discount_percent BETWEEN 1 AND 100 AND expires_at > starts_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.coupons');
    }
};
