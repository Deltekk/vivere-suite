<?php

namespace Modules\HumanResources\Notifications;

use App\Models\User;
use Filament\Facades\Filament;

/**
 * All'utente creato da un admin: benvenuto, imposta la tua password.
 * Il link è un normale link di reset password (scade come quelli, vedi config/auth.php).
 */
class WelcomeSetPassword extends HumanResourcesNotification
{
    public function __construct(public string $token) {}

    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    protected function isMandatory(): bool
    {
        return true;
    }

    protected function title(User $notifiable): string
    {
        return 'Benvenuto nella Suite Vivere';
    }

    protected function lines(User $notifiable): array
    {
        return [
            "Lo staff ti ha creato un account. Il tuo username è {$notifiable->username}.",
            'Per iniziare scegli una password con il pulsante qui sotto.',
        ];
    }

    protected function action(User $notifiable): ?array
    {
        return ['Scegli la password', Filament::getPanel('hr')->getResetPasswordUrl($this->token, $notifiable)];
    }
}
