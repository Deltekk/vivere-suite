<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.warehouse_products: prodotto in vendita in un magazzino.
 * Fornitore, prezzo, scorte e soglia di avviso sono per magazzino.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.warehouse_products', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->integer('price_cents');                          // Lo storico dei prezzi sta nelle righe di vendita e nell'audit
            $table->integer('stock_quantity')->default(0);           // Mai sotto zero: la vendita viene rifiutata
            $table->integer('low_stock_threshold')->nullable();      // Sotto soglia: mail ai gestori delle aulette del magazzino (D7)
            $table->timestampTz('delisted_at')->nullable();          // Tolto dalla vendita ma conservato

            // ----- Foreign Keys -----
            $table->foreignUuid('warehouse_id')->constrained('kaffettino.warehouses');
            $table->foreignUuid('product_id')->index()->constrained('kaffettino.products');
            $table->foreignUuid('supplier_id')->index()->constrained('kaffettino.suppliers'); // Può cambiare da magazzino a magazzino

            // ----- Timestamps -----
            $table->timestampsTz();

            // ----- Indexes -----
            $table->unique(['warehouse_id', 'product_id']);
        });

        DB::statement('ALTER TABLE kaffettino.warehouse_products ADD CONSTRAINT kaffettino_warehouse_products_values_check CHECK (price_cents >= 0 AND stock_quantity >= 0 AND (low_stock_threshold IS NULL OR low_stock_threshold >= 0))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.warehouse_products');
    }
};
