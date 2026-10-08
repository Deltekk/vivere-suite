<?php

namespace Modules\Kaffettino\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riga di un movimento (kaffettino.transaction_items), con il prezzo al momento della vendita.
 * Append-only come i movimenti.
 *
 * @property string $id
 * @property int $quantity
 * @property int $unit_price_cents
 * @property int $discount_cents
 * @property string $transaction_id
 * @property string $warehouse_product_id
 */
#[Table(name: 'kaffettino.transaction_items', timestamps: false)]
#[Fillable(['quantity', 'unit_price_cents', 'discount_cents', 'transaction_id', 'warehouse_product_id'])]
class TransactionItem extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_cents' => 'integer',
            'discount_cents' => 'integer',
        ];
    }

    /** @return BelongsTo<Transaction, $this> */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /** @return BelongsTo<WarehouseProduct, $this> */
    public function warehouseProduct(): BelongsTo
    {
        return $this->belongsTo(WarehouseProduct::class);
    }

    /**
     * Totale della riga in centesimi, sconto compreso.
     */
    public function totalCents(): int
    {
        return $this->quantity * $this->unit_price_cents - $this->discount_cents;
    }
}
