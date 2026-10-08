<?php

namespace App\Policies;

use App\Policies\Concerns\ManagesReferenceData;

/**
 * Permessi su App\Models\Course: lo staff consulta, gli admin gestiscono.
 */
class CoursePolicy
{
    use ManagesReferenceData;
}
