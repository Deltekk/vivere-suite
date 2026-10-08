<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Corso di studio (core.courses).
 *
 * @property string $id
 * @property string $name
 * @property string $department_id
 * @property string|null $auletta_id
 */
#[Table('core.courses')]
#[Fillable(['name', 'department_id', 'auletta_id'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use Audited, HasFactory, HasUuids;

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Auletta di afferenza (può mancare).
     *
     * @return BelongsTo<Auletta, $this>
     */
    public function auletta(): BelongsTo
    {
        return $this->belongsTo(Auletta::class);
    }

    /**
     * Studenti iscritti al corso.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Amministratori del corso (core.admin_courses).
     *
     * @return BelongsToMany<User, $this>
     */
    public function administrators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'core.admin_courses')->withPivot('created_at');
    }

    /**
     * Aule in cui si tengono le lezioni del corso.
     *
     * @return BelongsToMany<Classroom, $this>
     */
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'core.classroom_course')->withTimestamps();
    }
}
