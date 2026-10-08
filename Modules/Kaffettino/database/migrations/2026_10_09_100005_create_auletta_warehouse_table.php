<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.auletta_warehouse: aulette in cui si vende con Kaffettino e magazzino da cui
 * prendono i prodotti. Estende core.aulette (1:1) senza aggiungere colonne di Kaffettino al core.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.auletta_warehouse', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->foreignUuid('auletta_id')->primary()->constrained('core.aulette');

            // ----- Data -----
            $table->timestampTz('delisted_at')->nullable(); // Auletta tolta dalla vendita

            // ----- Foreign Keys -----
            $table->foreignUuid('warehouse_id')->index()->constrained('kaffettino.warehouses');

            // ----- Timestamps -----
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.auletta_warehouse');
    }
};
