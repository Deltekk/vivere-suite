<?php

namespace Modules\HumanResources\Actions;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

/**
 * Un admin cambia il ruolo di un utente (HR 2.2.4).
 *
 * Il ruolo SuperAdmin non si assegna da qui (esiste dall'installazione, SuperAdminSeeder) e il
 * super admin non si può declassare: lo garantisce UserPolicy::changeRole.
 * Chi diventa staff o admin dovrà completare il profilo staff al prossimo accesso.
 */
class ChangeUserRole
{
    public function handle(User $user, Role $role, User $changedBy): void
    {
        Gate::forUser($changedBy)->authorize('changeRole', $user);

        if ($role === Role::SuperAdmin) {
            throw new InvalidArgumentException('Il ruolo di super admin non si assegna dall\'interfaccia.');
        }

        $user->role = $role; // La modifica finisce nell'audit log tramite il trait Audited
        $user->save();
    }
}
