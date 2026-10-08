<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Dipartimento accademico (core.departments). Unità organizzativa, non edificio: vedi Building.
 *
 * @property string $id
 * @property string $name
 * @property string $macroarea_id
 */
#[Table('core.departments')]
#[Fillable(['name', 'macroarea_id'])]
class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use Audited, HasFactory, HasUuids;

    /** @return BelongsTo<Macroarea, $this> */
    public function macroarea(): BelongsTo
    {
        return $this->belongsTo(Macroarea::class);
    }

    /** @return HasMany<Course, $this> */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /** @return HasMany<Classroom, $this> */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }
}
