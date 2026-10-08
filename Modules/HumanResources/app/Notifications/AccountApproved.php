<?php

namespace Modules\HumanResources\Notifications;

use App\Models\User;
use Filament\Facades\Filament;

/**
 * All'utente: la registrazione è stata accettata.
 */
class AccountApproved extends HumanResourcesNotification
{
    protected function isMandatory(): bool
    {
        return true;
    }

    protected function title(User $notifiable): string
    {
        return 'Il tuo account Vivere è attivo';
    }

    protected function lines(User $notifiable): array
    {
        return [
            'La tua registrazione è stata accettata: da ora puoi usare i servizi della Suite Vivere.',
        ];
    }

    protected function action(User $notifiable): ?array
    {
        return ['Accedi', (string) Filament::getPanel('hr')->getUrl()];
    }
}
