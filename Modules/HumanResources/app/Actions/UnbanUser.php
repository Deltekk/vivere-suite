<?php

namespace Modules\HumanResources\Actions;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Modules\HumanResources\Notifications\AccountReactivated;

/**
 * Un admin revoca il ban: l'account torna attivo. Lo storico in core.bans resta.
 */
class UnbanUser
{
    public function handle(User $user, User $reactivatedBy): void
    {
        Gate::forUser($reactivatedBy)->authorize('unban', $user);

        $user->status = UserStatus::Active;
        $user->save();

        activity('humanresources')->performedOn($user)->causedBy($reactivatedBy)->event('unbanned')->log('Ban revocato');

        $user->notify(new AccountReactivated);
    }
}
