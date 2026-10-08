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
use Modules\Kaffettino\Database\Factories\WarehouseFactory;

/**
 * Magazzino (kaffettino.warehouses): saldo e debito massimo dei conti sono legati al magazzino.
 *
 * @property string $id
 * @property string $name
 * @property int $max_debt_cents
 * @property CarbonImmutable|null $delisted_at
 * @property string|null $birthday_product_id
 */
#[Table('kaffettino.warehouses')]
#[Fillable(['name', 'max_debt_cents', 'delisted_at', 'birthday_product_id'])]
#[UseFactory(WarehouseFactory::class)]
class Warehouse extends Model
{
    /** @use HasFactory<WarehouseFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'max_debt_cents' => 'integer',
            'delisted_at' => 'datetime',
        ];
    }

    /**
     * Aulette che vendono con questo magazzino.
     *
     * @return HasMany<AulettaWarehouse, $this>
     */
    public function aulette(): HasMany
    {
        return $this->hasMany(AulettaWarehouse::class);
    }

    /** @return HasMany<WarehouseProduct, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(WarehouseProduct::class);
    }

    /** @return HasMany<Account, $this> */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Prodotto offerto al compleanno (il caffè).
     *
     * @return BelongsTo<Product, $this>
     */
    public function birthdayProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'birthday_product_id');
    }
}
