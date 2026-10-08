<?php

namespace Modules\HumanResources\Listeners;

use App\Enums\Role;
use App\Models\User;
use Filament\Auth\Events\Registered;
use Illuminate\Support\Facades\Notification;
use Modules\HumanResources\Notifications\RegistrationAwaitingReview;

/**
 * Dopo una registrazione avvisa gli admin del corso dello studente, esortandoli ad accettarla
 * (HR 2.2.1). Se il corso non ha admin (o lo studente è delle superiori) avvisa tutti gli admin,
 * così nessuna richiesta resta senza risposta.
 */
class NotifyRegistrationReviewers
{
    public function handle(Registered $event): void
    {
        $applicant = $event->getUser();

        if (! $applicant instanceof User) {
            return;
        }

        $reviewers = $applicant->course?->administrators()->where('status', 'Active')->get() ?? collect();

        if ($reviewers->isEmpty()) {
            $reviewers = User::query()
                ->whereIn('role', [Role::Admin, Role::SuperAdmin])
                ->where('status', 'Active')
                ->get();
        }

        Notification::send($reviewers, new RegistrationAwaitingReview($applicant));
    }
}
