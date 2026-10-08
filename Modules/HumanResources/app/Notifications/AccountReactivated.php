<?php

namespace Modules\HumanResources\Notifications;

use App\Models\User;
use Filament\Facades\Filament;

/**
 * All'utente: il ban è stato revocato e l'account è di nuovo attivo.
 */
class AccountReactivated extends HumanResourcesNotification
{
    protected function isMandatory(): bool
    {
        return true;
    }

    protected function title(User $notifiable): string
    {
        return 'Il tuo account Vivere è di nuovo attivo';
    }

    protected function lines(User $notifiable): array
    {
        return ['Un amministratore ha revocato la sospensione del tuo account: puoi di nuovo usare i servizi della Suite Vivere.'];
    }

    protected function action(User $notifiable): ?array
    {
        return ['Accedi', (string) Filament::getPanel('hr')->getUrl()];
    }
}
