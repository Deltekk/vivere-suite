<?php

namespace Modules\Kaffettino\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Impostazioni Kaffettino di una persona (kaffettino.user_settings): la chiave è l'id dell'utente.
 *
 * @property string $user_id
 * @property CarbonImmutable|null $statistics_consent_at
 */
#[Table(name: 'kaffettino.user_settings', key: 'user_id', keyType: 'string', incrementing: false)]
#[Fillable(['user_id', 'statistics_consent_at'])]
class UserSetting extends Model
{
    protected function casts(): array
    {
        return [
            'statistics_consent_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vero se la persona ha acconsentito alle statistiche anonime.
     */
    public function participatesInStatistics(): bool
    {
        return $this->statistics_consent_at !== null;
    }
}
