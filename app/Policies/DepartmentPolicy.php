<?php

namespace App\Policies;

use App\Policies\Concerns\ManagesReferenceData;

/**
 * Permessi su App\Models\Department: lo staff consulta, gli admin gestiscono.
 */
class DepartmentPolicy
{
    use ManagesReferenceData;
}
