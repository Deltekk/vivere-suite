<?php

namespace Modules\Kaffettino\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Kaffettino\Database\Factories\SupplierFactory;

/**
 * Fornitore (kaffettino.suppliers).
 *
 * @property string $id
 * @property string $name
 * @property string $address
 * @property string|null $phone_number
 * @property string|null $email
 */
#[Table('kaffettino.suppliers')]
#[Fillable(['name', 'address', 'phone_number', 'email'])]
#[UseFactory(SupplierFactory::class)]
class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory, HasUuids;

    /** @return HasMany<WarehouseProduct, $this> */
    public function warehouseProducts(): HasMany
    {
        return $this->hasMany(WarehouseProduct::class);
    }
}
