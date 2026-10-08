<?php

namespace App\Policies;

use App\Policies\Concerns\ManagesReferenceData;

/**
 * Permessi su App\Models\Macroarea: lo staff consulta, gli admin gestiscono.
 */
class MacroareaPolicy
{
    use ManagesReferenceData;
}
