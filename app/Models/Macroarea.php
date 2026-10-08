<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Database\Factories\MacroareaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Macroarea dell'ateneo (core.macroareas): Ingegneria, Medicina, Economia, ...
 *
 * @property string $id
 * @property string $name
 */
#[Table('core.macroareas')]
#[Fillable(['name'])]
class Macroarea extends Model
{
    /** @use HasFactory<MacroareaFactory> */
    use Audited, HasFactory, HasUuids;

    /** @return HasMany<Department, $this> */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
