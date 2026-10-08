<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Database\Factories\StaffProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dati richiesti solo a staff e amministratori (core.staff_profiles, 1:1 con User).
 *
 * @property string $id
 * @property string $tax_code
 * @property string $birth_city
 * @property string $birth_province
 * @property string $birth_country
 * @property string $user_id
 */
#[Table('core.staff_profiles')]
#[Fillable(['tax_code', 'birth_city', 'birth_province', 'birth_country', 'user_id'])]
class StaffProfile extends Model
{
    /** @use HasFactory<StaffProfileFactory> */
    use Audited, HasFactory, HasUuids;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
