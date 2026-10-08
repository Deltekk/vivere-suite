<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ban di un utente (core.bans). Storico append-only: non si modifica né si cancella.
 *
 * @property string $id
 * @property string $reason
 * @property string $email_pattern
 * @property string $user_id
 * @property string $banned_by
 * @property CarbonImmutable $created_at
 */
#[Table('core.bans')]
#[Fillable(['reason', 'email_pattern', 'user_id', 'banned_by'])]
class Ban extends Model
{
    use Audited, HasUuids;

    /** La tabella ha solo created_at. */
    public const UPDATED_AT = null;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Admin che ha eseguito il ban.
     *
     * @return BelongsTo<User, $this>
     */
    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }
}
