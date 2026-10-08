<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Aula dell'ateneo (core.classrooms).
 *
 * @property string $id
 * @property string|null $name
 * @property string $code
 * @property string $building_id
 * @property string|null $department_id
 */
#[Table('core.classrooms')]
#[Fillable(['name', 'code', 'building_id', 'department_id'])]
class Classroom extends Model
{
    use Audited, HasUuids;

    /** @return BelongsTo<Building, $this> */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsToMany<Course, $this> */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'core.classroom_course')->withTimestamps();
    }
}
