<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Activity;
use App\Models\User;

/**
 * Audit log: lo leggono solo i super admin (preambolo). Nessuno lo modifica, nemmeno loro.
 */
class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === Role::SuperAdmin;
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->role === Role::SuperAdmin;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Activity $activity): bool
    {
        return false;
    }

    public function delete(User $user, Activity $activity): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
