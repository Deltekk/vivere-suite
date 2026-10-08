<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.products: catalogo comune a tutti i magazzini.
 * Un prodotto creato non si cancella mai (storicità delle vendite): si delista per magazzino
 * (warehouse_products.delisted_at). Prezzo, fornitore e scorte dipendono dal magazzino.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.products', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('name');

            // ----- Foreign Keys -----
            $table->foreignUuid('category_id')->index()->constrained('kaffettino.product_categories');

            // ----- Timestamps -----
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.products');
    }
};
