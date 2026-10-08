<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.coupon_product: prodotti (di un magazzino) su cui vale un coupon.
 * Tabella pivot: chiave primaria composta.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.coupon_product', function (Blueprint $table) {
            // ----- Foreign Keys -----
            $table->foreignUuid('coupon_id')->constrained('kaffettino.coupons')->cascadeOnDelete();
            $table->foreignUuid('warehouse_product_id')->index()->constrained('kaffettino.warehouse_products');

            // ----- Primary keys -----
            $table->primary(['coupon_id', 'warehouse_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.coupon_product');
    }
};
