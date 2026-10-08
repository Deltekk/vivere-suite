<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Carbon\CarbonImmutable;
use Database\Factories\UserAcademicRoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mandato istituzionale di un utente (core.user_academic_roles).
 *
 * Non è una semplice pivot: la stessa persona può avere la stessa carica più volte
 * in periodi diversi, e lo storico dei mandati scaduti va conservato.
 *
 * @property string $id
 * @property CarbonImmutable $started_at
 * @property CarbonImmutable $expires_at
 * @property string $user_id
 * @property string $academic_role_id
 */
#[Table('core.user_academic_roles')]
#[Fillable(['started_at', 'expires_at', 'user_id', 'academic_role_id'])]
class UserAcademicRole extends Model
{
    /** @use HasFactory<UserAcademicRoleFactory> */
    use Audited, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<AcademicRole, $this> */
    public function academicRole(): BelongsTo
    {
        return $this->belongsTo(AcademicRole::class);
    }

    /**
     * Mandati in corso oggi.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $today = now(config('vivere.display_timezone'))->toDateString();

        $query->where('started_at', '<=', $today)->where('expires_at', '>=', $today);
    }
}
