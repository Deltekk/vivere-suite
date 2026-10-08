<?php

namespace App\Policies;

use App\Policies\Concerns\ManagesReferenceData;

/**
 * Permessi su App\Models\School: lo staff consulta, gli admin gestiscono.
 */
class SchoolPolicy
{
    use ManagesReferenceData;
}
