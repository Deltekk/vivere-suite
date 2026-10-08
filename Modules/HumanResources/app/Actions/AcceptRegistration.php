<?php

namespace Modules\HumanResources\Actions;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Modules\HumanResources\Notifications\AccountApproved;

/**
 * Uno staffer accetta una registrazione in attesa (D3): l'account diventa attivo.
 */
class AcceptRegistration
{
    public function handle(User $user, User $acceptedBy): void
    {
        Gate::forUser($acceptedBy)->authorize('accept', $user);

        $user->status = UserStatus::Active;
        $user->course_year_confirmed_at = now(); // Ha appena dichiarato il suo anno di corso
        $user->save();

        activity('humanresources')->performedOn($user)->causedBy($acceptedBy)->event('accepted')->log('Registrazione accettata');

        $user->notify(new AccountApproved);
    }
}
