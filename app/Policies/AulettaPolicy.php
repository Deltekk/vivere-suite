<?php

namespace App\Policies;

use App\Policies\Concerns\ManagesReferenceData;

/**
 * Permessi su App\Models\Auletta: lo staff consulta, gli admin gestiscono.
 */
class AulettaPolicy
{
    use ManagesReferenceData;
}
