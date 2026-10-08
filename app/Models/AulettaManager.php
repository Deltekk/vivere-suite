<?php

namespace App\Models;

use App\Enums\KeyState;
use App\Models\Concerns\Audited;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Gestione di un'auletta da parte di un utente non studente (core.auletta_managers), con storico.
 *
 * @property string $id
 * @property KeyState $keys_state
 * @property CarbonImmutable $assigned_at
 * @property CarbonImmutable|null $removed_at
 * @property string $user_id
 * @property string $auletta_id
 */
#[Table('core.auletta_managers')]
#[Fillable(['keys_state', 'assigned_at', 'removed_at', 'user_id', 'auletta_id'])]
class AulettaManager extends Model
{
    use Audited, HasUuids;

    protected function casts(): array
    {
        return [
            'keys_state' => KeyState::class,
            'assigned_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Auletta, $this> */
    public function auletta(): BelongsTo
    {
        return $this->belongsTo(Auletta::class);
    }
}
