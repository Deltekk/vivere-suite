<?php

namespace Modules\HumanResources\Notifications;

use App\Models\User;

/**
 * Comunicazione inviata da un admin a molti utenti insieme (HR 2.2.4).
 * Per mail arriva solo a chi non ha disattivato le email del servizio HR.
 */
class BroadcastMessage extends HumanResourcesNotification
{
    public function __construct(
        public string $subject,
        public string $body,
    ) {}

    protected function title(User $notifiable): string
    {
        return $this->subject;
    }

    protected function lines(User $notifiable): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', $this->body) ?: [])));
    }
}
