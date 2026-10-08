<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * kaffettino.transactions: movimenti di denaro e merce.
 *
 * Append-only a livello di DB (trigger core.prevent_changes): non si modificano né si cancellano,
 * si correggono con un movimento Adjustment. L'id può essere generato dall'ESP32 (UUIDv7): se il
 * dispositivo ritrasmette lo stesso acquisto (rete congestionata, coda offline) la chiave primaria
 * impedisce di registrarlo due volte.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kaffettino.transactions', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->enum('type', ['Purchase', 'TopUp', 'Gift', 'BirthdayGift', 'Adjustment']); // Modules\Kaffettino\Enums\TransactionType
            $table->enum('channel', ['Embedded', 'Web']);                                      // Modules\Kaffettino\Enums\TransactionChannel
            $table->bigInteger('amount_cents');         // Con segno: + ricarica, - acquisto/omaggio, 0 compleanno
            $table->bigInteger('balance_after_cents');  // Saldo del conto dopo il movimento
            $table->timestampTz('occurred_at');         // Quando è avvenuto sul dispositivo (può precedere created_at)
            $table->text('note')->nullable();

            // ----- Foreign Keys -----
            $table->foreignUuid('account_id')->constrained('kaffettino.accounts');                 // Per gli omaggi: di norma il conto dell'auletta
            $table->foreignUuid('warehouse_id')->constrained('kaffettino.warehouses');
            $table->foreignUuid('auletta_id')->nullable()->constrained('core.aulette');            // NULL per le ricariche da portale
            $table->foreignUuid('device_id')->nullable()->index()->constrained('core.devices');
            $table->foreignUuid('coupon_id')->nullable()->index()->constrained('kaffettino.coupons'); // Non cumulabili: al massimo uno
            $table->foreignUuid('performed_by')->nullable()->constrained('core.users');            // Admin di ricarica/omaggio/rettifica

            // ----- Timestamps -----
            $table->timestampTz('created_at')->useCurrent();

            // ----- Indexes -----
            $table->index(['account_id', 'occurred_at']);
            $table->index(['warehouse_id', 'occurred_at']); // Resoconti per magazzino e periodo
            $table->index(['auletta_id', 'occurred_at']);   // Resoconti per auletta e periodo
        });

        // Il segno dell'importo deve essere coerente con il tipo di movimento
        DB::statement(<<<'SQL'
            ALTER TABLE kaffettino.transactions ADD CONSTRAINT kaffettino_transactions_amount_check CHECK (
                (type = 'Purchase' AND amount_cents < 0)
                OR (type = 'TopUp' AND amount_cents > 0)
                OR (type = 'Gift' AND amount_cents <= 0)
                OR (type = 'BirthdayGift' AND amount_cents = 0)
                OR (type = 'Adjustment' AND amount_cents <> 0 AND note IS NOT NULL)
            )
            SQL);
        DB::statement('CREATE TRIGGER kaffettino_transactions_append_only BEFORE UPDATE OR DELETE ON kaffettino.transactions FOR EACH ROW EXECUTE FUNCTION core.prevent_changes()');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaffettino.transactions');
    }
};
