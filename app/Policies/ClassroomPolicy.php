<?php

namespace App\Policies;

use App\Policies\Concerns\ManagesReferenceData;

/**
 * Permessi su App\Models\Classroom: lo staff consulta, gli admin gestiscono.
 */
class ClassroomPolicy
{
    use ManagesReferenceData;
}
