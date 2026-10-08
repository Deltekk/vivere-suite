<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.warehouses: magazzini. Saldo e debito massimo sono legati al magazzino (D6),
 * condiviso da più aulette (es. Ingegneria: 2° piano, 3° piano e DEIM).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.warehouses', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('name')->unique();
            $table->integer('max_debt_cents'); // Debito massimo dei conti personali; alla creazione si propone 3 × il prezzo del caffè
            $table->timestampTz('delisted_at')->nullable(); // Magazzino dismesso: non si cancella per lo storico

            // ----- Foreign Keys -----
            $table->foreignUuid('birthday_product_id')->nullable()->constrained('kaffettino.products'); // Prodotto offerto al compleanno

            // ----- Timestamps -----
            $table->timestampsTz();
        });

        DB::statement('ALTER TABLE kaffettino.warehouses ADD CONSTRAINT kaffettino_warehouses_max_debt_check CHECK (max_debt_cents >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.warehouses');
    }
};
