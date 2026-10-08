<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.transaction_items: righe di un movimento, con il prezzo "fotografato" al momento
 * della vendita. Append-only come i movimenti.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.transaction_items', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->integer('quantity');
            $table->integer('unit_price_cents');
            $table->integer('discount_cents')->default(0); // Sconto totale della riga (coupon)

            // ----- Foreign Keys -----
            $table->foreignUuid('transaction_id')->index()->constrained('kaffettino.transactions');
            $table->foreignUuid('warehouse_product_id')->index()->constrained('kaffettino.warehouse_products');
        });

        DB::statement('ALTER TABLE kaffettino.transaction_items ADD CONSTRAINT kaffettino_transaction_items_values_check CHECK (quantity > 0 AND unit_price_cents >= 0 AND discount_cents >= 0 AND discount_cents <= quantity * unit_price_cents)');
        DB::statement('CREATE TRIGGER kaffettino_transaction_items_append_only BEFORE UPDATE OR DELETE ON kaffettino.transaction_items FOR EACH ROW EXECUTE FUNCTION core.prevent_changes()');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.transaction_items');
    }
};
