<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Preferenza email di un utente per un servizio (core.email_preferences).
 *
 * @property string $id
 * @property string $service
 * @property bool $enabled
 * @property string $user_id
 */
#[Table('core.email_preferences')]
#[Fillable(['service', 'enabled', 'user_id'])]
class EmailPreference extends Model
{
    use Audited, HasUuids;

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
