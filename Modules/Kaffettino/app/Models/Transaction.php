<?php

namespace Modules\Kaffettino\Models;

use App\Models\Auletta;
use App\Models\Device;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Kaffettino\Enums\TransactionChannel;
use Modules\Kaffettino\Enums\TransactionType;

/**
 * Movimento di denaro e merce (kaffettino.transactions).
 *
 * Append-only: un trigger del DB rifiuta UPDATE e DELETE, le correzioni sono movimenti
 * Adjustment. L'id può arrivare dall'ESP32 (UUIDv7): se il dispositivo ritrasmette lo stesso
 * acquisto, la chiave primaria impedisce di registrarlo due volte. HasUuids genera l'id
 * solo se non è già impostato.
 *
 * @property string $id
 * @property TransactionType $type
 * @property TransactionChannel $channel
 * @property int $amount_cents
 * @property int $balance_after_cents
 * @property CarbonImmutable $occurred_at
 * @property string|null $note
 * @property string $account_id
 * @property string $warehouse_id
 * @property string|null $auletta_id
 * @property string|null $device_id
 * @property string|null $coupon_id
 * @property string|null $performed_by
 * @property CarbonImmutable $created_at
 */
#[Table('kaffettino.transactions')]
#[Fillable([
    'id', 'type', 'channel', 'amount_cents', 'balance_after_cents', 'occurred_at', 'note',
    'account_id', 'warehouse_id', 'auletta_id', 'device_id', 'coupon_id', 'performed_by',
])]
class Transaction extends Model
{
    use HasUuids;

    /** La tabella ha solo created_at (le righe non si aggiornano mai). */
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'channel' => TransactionChannel::class,
            'amount_cents' => 'integer',
            'balance_after_cents' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** @return BelongsTo<Warehouse, $this> */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** @return BelongsTo<Auletta, $this> */
    public function auletta(): BelongsTo
    {
        return $this->belongsTo(Auletta::class);
    }

    /** @return BelongsTo<Device, $this> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /** @return BelongsTo<Coupon, $this> */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Admin che ha eseguito ricarica, omaggio o rettifica.
     *
     * @return BelongsTo<User, $this>
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /** @return HasMany<TransactionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
