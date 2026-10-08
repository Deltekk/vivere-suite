<?php

namespace Modules\Kaffettino\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Caffè gratis del compleanno (kaffettino.birthday_gifts): uno all'anno per persona (vincolo DB).
 * La chiave è il movimento BirthdayGift corrispondente.
 *
 * @property string $transaction_id
 * @property int $year
 * @property string $user_id
 */
#[Table(name: 'kaffettino.birthday_gifts', key: 'transaction_id', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['transaction_id', 'year', 'user_id'])]
class BirthdayGift extends Model
{
    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    /** @return BelongsTo<Transaction, $this> */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
