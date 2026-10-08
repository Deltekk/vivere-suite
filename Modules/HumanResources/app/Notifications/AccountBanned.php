<?php

namespace Modules\HumanResources\Notifications;

use App\Models\User;

/**
 * All'utente: è stato bannato, con la motivazione scritta dall'admin (HR 2.2.1).
 */
class AccountBanned extends HumanResourcesNotification
{
    public function __construct(public string $reason) {}

    /**
     * Va solo per mail: l'utente bannato non può più entrare a leggere la campanella.
     */
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
        return 'Il tuo account Vivere è stato sospeso';
    }

    protected function lines(User $notifiable): array
    {
        return [
            'Il tuo account è stato sospeso da un amministratore per questo motivo:',
            $this->reason,
            'Se pensi che si tratti di un errore, rispondi a questa mail o contatta lo staff della tua auletta.',
        ];
    }
}
