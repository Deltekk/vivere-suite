<?php

namespace Modules\HumanResources\Notifications;

use App\Models\Auletta;
use App\Models\User;

/**
 * Al gestore rimosso: vieni in auletta a restituire le chiavi (HR 2.2.2).
 */
class AulettaKeysReturn extends HumanResourcesNotification
{
    public function __construct(public Auletta $auletta) {}

    protected function isMandatory(): bool
    {
        return true;
    }

    protected function title(User $notifiable): string
    {
        return "Non gestisci più l'auletta {$this->auletta->name}";
    }

    protected function lines(User $notifiable): array
    {
        return [
            "Non fai più parte dei gestori dell'auletta {$this->auletta->name}. Grazie per il tuo lavoro!",
            'Passa in auletta appena puoi per restituire le chiavi.',
        ];
    }
}
