<?php

namespace Modules\Kaffettino\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Kaffettino\Database\Factories\WarehouseProductFactory;

/**
 * Prodotto in vendita in un magazzino (kaffettino.warehouse_products): fornitore, prezzo,
 * scorte e soglia sono per magazzino. Le scorte non possono scendere sotto zero (vincolo DB).
 *
 * @property string $id
 * @property int $price_cents
 * @property int $stock_quantity
 * @property int|null $low_stock_threshold
 * @property CarbonImmutable|null $delisted_at
 * @property string $warehouse_id
 * @property string $product_id
 * @property string $supplier_id
 */
#[Table('kaffettino.warehouse_products')]
#[Fillable(['price_cents', 'stock_quantity', 'low_stock_threshold', 'delisted_at', 'warehouse_id', 'product_id', 'supplier_id'])]
#[UseFactory(WarehouseProductFactory::class)]
class WarehouseProduct extends Model
{
    /** @use HasFactory<WarehouseProductFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'delisted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Warehouse, $this> */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<Supplier, $this> */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /** @return HasMany<StockMovement, $this> */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Vero se le scorte sono alla soglia di avviso o sotto (D7: mail ai gestori delle aulette).
     */
    public function isLowOnStock(): bool
    {
        return $this->low_stock_threshold !== null && $this->stock_quantity <= $this->low_stock_threshold;
    }
}
