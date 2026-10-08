<?php

namespace Modules\HumanResources\Notifications;

use App\Models\Auletta;
use App\Models\User;

/**
 * Al nuovo gestore: vieni in auletta a prendere le chiavi (HR 2.2.2).
 */
class AulettaKeysPickup extends HumanResourcesNotification
{
    public function __construct(public Auletta $auletta) {}

    protected function isMandatory(): bool
    {
        return true;
    }

    protected function title(User $notifiable): string
    {
        return "Sei {$notifiable->inflect('il nuovo gestore', 'la nuova gestrice', 'nella gestione')} dell'auletta {$this->auletta->name}";
    }

    protected function lines(User $notifiable): array
    {
        return [
            "Da oggi gestisci l'auletta {$this->auletta->name}.",
            'Passa in auletta appena puoi per ritirare le chiavi: riceverai qui le comunicazioni sulla gestione.',
        ];
    }
}
