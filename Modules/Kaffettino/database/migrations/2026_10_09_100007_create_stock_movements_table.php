<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.stock_movements: carichi e rettifiche di magazzino (le uscite per vendita stanno
 * in transaction_items). Append-only a livello di DB: le correzioni sono nuove righe.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.stock_movements', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->integer('quantity');                         // Positivo = carico, negativo = perdita/correzione
            $table->enum('reason', ['Restock', 'Loss', 'Correction']); // Modules\Kaffettino\Enums\StockReason
            $table->integer('unit_cost_cents')->nullable();      // Costo di acquisto, per le previsioni di guadagno
            $table->text('note')->nullable();

            // ----- Foreign Keys -----
            $table->foreignUuid('warehouse_product_id')->index()->constrained('kaffettino.warehouse_products');
            $table->foreignUuid('performed_by')->constrained('core.users');

            // ----- Timestamps -----
            $table->timestampTz('created_at')->useCurrent();
        });

        DB::statement('ALTER TABLE kaffettino.stock_movements ADD CONSTRAINT kaffettino_stock_movements_values_check CHECK (quantity <> 0 AND (unit_cost_cents IS NULL OR unit_cost_cents >= 0))');
        DB::statement('CREATE TRIGGER kaffettino_stock_movements_append_only BEFORE UPDATE OR DELETE ON kaffettino.stock_movements FOR EACH ROW EXECUTE FUNCTION core.prevent_changes()');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.stock_movements');
    }
};
