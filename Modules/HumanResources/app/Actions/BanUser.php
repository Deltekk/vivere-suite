<?php

namespace Modules\HumanResources\Actions;

use App\Enums\UserStatus;
use App\Models\Ban;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\HumanResources\Notifications\AccountBanned;
use Modules\HumanResources\Support\UnipaIdentity;

/**
 * Un admin banna un utente con una motivazione obbligatoria, che gli arriva per mail (HR 2.2.1).
 *
 * Il ban resta nello storico (core.bans) con il "pattern" della mail: se la stessa persona si
 * registra di nuovo con un'altra mail istituzionale, allo staff compare un warning (D2).
 */
class BanUser
{
    public function handle(User $user, string $reason, User $bannedBy): Ban
    {
        Gate::forUser($bannedBy)->authorize('ban', $user);

        $ban = DB::transaction(function () use ($user, $reason, $bannedBy): Ban {
            $user->status = UserStatus::Banned;
            $user->save();

            return Ban::create([
                'reason' => $reason,
                'email_pattern' => UnipaIdentity::emailPattern($user->email),
                'user_id' => $user->id,
                'banned_by' => $bannedBy->id,
            ]);
        });

        activity('humanresources')->performedOn($user)->causedBy($bannedBy)->event('banned')
            ->withProperties(['reason' => $reason])->log('Utente bannato');

        $user->notify(new AccountBanned($reason));

        return $ban;
    }
}
