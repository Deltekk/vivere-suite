<?php

namespace App\Policies;

use App\Policies\Concerns\ManagesReferenceData;

/**
 * Permessi su App\Models\AcademicRole: lo staff consulta, gli admin gestiscono.
 */
class AcademicRolePolicy
{
    use ManagesReferenceData;
}
