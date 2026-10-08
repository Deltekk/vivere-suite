<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Voce di changelog di una piattaforma (core.changelogs).
 *
 * @property string $id
 * @property string $service
 * @property string $title
 * @property string $body
 * @property CarbonImmutable $published_at
 */
#[Table('core.changelogs')]
#[Fillable(['service', 'title', 'body', 'published_at'])]
class Changelog extends Model
{
    use Audited, HasUuids;

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Utenti che hanno già visto questa voce.
     *
     * @return BelongsToMany<User, $this>
     */
    public function viewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'core.changelog_user')->withPivot('seen_at');
    }
}
