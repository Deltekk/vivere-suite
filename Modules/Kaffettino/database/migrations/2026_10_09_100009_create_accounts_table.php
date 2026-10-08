<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.accounts: conti. Intestati a una persona (uno per magazzino, solo staff, D8)
 * oppure a un'auletta. Il conto dell'auletta raccoglie gli omaggi agli ospiti: va in negativo
 * senza limite, non riceve le mail sui debiti e non ha card.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.accounts', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            // Negativo = debito. Si aggiorna nella stessa transazione DB del movimento, con lock sulla riga
            $table->bigInteger('balance_cents')->default(0);
            $table->timestampTz('closed_at')->nullable();

            // ----- Foreign Keys -----
            $table->foreignUuid('user_id')->nullable()->constrained('core.users');      // Conto personale
            $table->foreignUuid('auletta_id')->nullable()->unique()->constrained('core.aulette'); // Conto dell'auletta (omaggi)
            $table->foreignUuid('warehouse_id')->index()->constrained('kaffettino.warehouses');
            $table->foreignUuid('opened_by')->constrained('core.users');

            // ----- Timestamps -----
            $table->timestampsTz();

            // ----- Indexes -----
            $table->unique(['user_id', 'warehouse_id']); // Un conto personale per magazzino (i NULL dei conti auletta non collidono)
        });

        // Ogni conto ha esattamente un titolare: una persona oppure un'auletta
        DB::statement('ALTER TABLE kaffettino.accounts ADD CONSTRAINT kaffettino_accounts_owner_check CHECK (num_nonnulls(user_id, auletta_id) = 1)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.accounts');
    }
};
