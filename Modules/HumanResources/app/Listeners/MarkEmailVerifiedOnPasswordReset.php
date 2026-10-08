<?php

namespace Modules\HumanResources\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;

/**
 * Chi reimposta la password ha cliccato un link arrivato alla sua mail: questo dimostra che la
 * mail è sua, quindi la segniamo come verificata. Serve soprattutto agli utenti creati dallo
 * staff (WelcomeSetPassword), che altrimenti dovrebbero verificare la mail una seconda volta.
 */
class MarkEmailVerifiedOnPasswordReset
{
    public function handle(PasswordReset $event): void
    {
        if ($event->user instanceof User && ! $event->user->hasVerifiedEmail()) {
            $event->user->markEmailAsVerified();
        }
    }
}
