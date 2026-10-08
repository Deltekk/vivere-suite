<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Database\Factories\AulettaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Auletta dell'associazione (core.aulette). Termine di dominio, non tradotto.
 *
 * @property string $id
 * @property string $name
 * @property float|null $lat
 * @property float|null $lon
 * @property string $building_id
 */
#[Table('core.aulette')]
#[Fillable(['name', 'lat', 'lon', 'building_id'])]
class Auletta extends Model
{
    /** @use HasFactory<AulettaFactory> */
    use Audited, HasFactory, HasUuids;

    /** @return BelongsTo<Building, $this> */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * Corsi che afferiscono a questa auletta.
     *
     * @return HasMany<Course, $this>
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Storico dei gestori (per i soli attivi filtrare removed_at NULL).
     *
     * @return HasMany<AulettaManager, $this>
     */
    public function managers(): HasMany
    {
        return $this->hasMany(AulettaManager::class);
    }
}
