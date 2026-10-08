<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Scuola superiore di provenienza (core.schools).
 *
 * @property string $id
 * @property string $name
 * @property string $city
 * @property float|null $lat
 * @property float|null $lon
 */
#[Table('core.schools')]
#[Fillable(['name', 'city', 'lat', 'lon'])]
class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use Audited, HasFactory, HasUuids;

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
