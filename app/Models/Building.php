<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Database\Factories\BuildingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Edificio fisico dell'ateneo (core.buildings), es. "Edificio 8".
 *
 * @property string $id
 * @property string $name
 * @property int|null $number
 * @property float|null $lat
 * @property float|null $lon
 */
#[Table('core.buildings')]
#[Fillable(['name', 'number', 'lat', 'lon'])]
class Building extends Model
{
    /** @use HasFactory<BuildingFactory> */
    use Audited, HasFactory, HasUuids;

    /** @return HasMany<Auletta, $this> */
    public function aulette(): HasMany
    {
        return $this->hasMany(Auletta::class);
    }

    /** @return HasMany<Classroom, $this> */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }
}
