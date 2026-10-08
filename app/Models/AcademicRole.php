<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Database\Factories\AcademicRoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Carica istituzionale (core.academic_roles): CDD, CCS, CDA, ...
 *
 * @property string $id
 * @property string $role
 */
#[Table('core.academic_roles')]
#[Fillable(['role'])]
class AcademicRole extends Model
{
    /** @use HasFactory<AcademicRoleFactory> */
    use Audited, HasFactory, HasUuids;

    /**
     * Mandati di questa carica.
     *
     * @return HasMany<UserAcademicRole, $this>
     */
    public function userAcademicRoles(): HasMany
    {
        return $this->hasMany(UserAcademicRole::class);
    }
}
