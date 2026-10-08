<?php

namespace App\Policies;

use App\Policies\Concerns\ManagesReferenceData;

/**
 * Permessi su App\Models\Building: lo staff consulta, gli admin gestiscono.
 */
class BuildingPolicy
{
    use ManagesReferenceData;
}
