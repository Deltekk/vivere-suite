<?php

namespace Modules\Kaffettino\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Kaffettino\Database\Factories\CardFactory;

/**
 * Card NFC (kaffettino.cards). Una sola card attiva per persona (indice parziale nel DB).
 *
 * Il PIN è facoltativo (D9): si salva solo il suo hash (cast "hashed"), si reimposta via mail
 * e non si può recuperare.
 *
 * @property string $id
 * @property string $uid
 * @property string|null $pin_hash
 * @property int $pin_failed_attempts
 * @property CarbonImmutable $assigned_at
 * @property CarbonImmutable|null $revoked_at
 * @property string $user_id
 * @property string $assigned_by
 */
#[Table('kaffettino.cards')]
#[Fillable(['uid', 'assigned_at', 'user_id', 'assigned_by'])]
#[Hidden(['pin_hash'])]
#[UseFactory(CardFactory::class)]
class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'pin_hash' => 'hashed',
            'pin_failed_attempts' => 'integer',
            'assigned_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Admin che ha assegnato la card.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Card non revocate.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNull('revoked_at');
    }

    public function hasPin(): bool
    {
        return $this->pin_hash !== null;
    }
}
