<?php

namespace Modules\Kaffettino\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Coupon di sconto (kaffettino.coupons): non cumulabile, valido solo sui prodotti scelti,
 * con scadenza obbligatoria e utilizzabile una sola volta per persona.
 *
 * @property string $id
 * @property string $code
 * @property int $discount_percent
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $expires_at
 * @property string $created_by
 */
#[Table('kaffettino.coupons')]
#[Fillable(['code', 'discount_percent', 'starts_at', 'expires_at', 'created_by'])]
class Coupon extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'discount_percent' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Prodotti (di un magazzino) su cui vale il coupon.
     *
     * @return BelongsToMany<WarehouseProduct, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(WarehouseProduct::class, 'kaffettino.coupon_product');
    }

    /**
     * Persone che hanno attivato il coupon (al massimo una volta ciascuna).
     *
     * @return BelongsToMany<User, $this>
     */
    public function activations(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kaffettino.coupon_activations')
            ->withPivot('activated_at', 'used_at', 'transaction_id');
    }

    /** @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
