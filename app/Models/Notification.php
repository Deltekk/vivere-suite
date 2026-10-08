<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Notifica in piattaforma (core.notifications).
 *
 * È la DatabaseNotification standard di Laravel spostata nello schema core:
 * User::notifications() usa questa classe, quindi il canale "database" di Laravel e la
 * campanella di Filament funzionano senza modifiche.
 */
#[Table('core.notifications')]
class Notification extends DatabaseNotification
{
    /**
     * Documenti allegati alla notifica.
     *
     * @return HasMany<NotificationAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(NotificationAttachment::class);
    }
}
