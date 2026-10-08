<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Changelog;
use App\Models\User;

/**
 * Changelog delle piattaforme: li scrivono gli admin.
 */
class ChangelogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRoleAtLeast(Role::Admin);
    }

    public function view(User $user, Changelog $changelog): bool
    {
        return $user->hasRoleAtLeast(Role::Admin);
    }

    public function create(User $user): bool
    {
        return $user->hasRoleAtLeast(Role::Admin);
    }

    public function update(User $user, Changelog $changelog): bool
    {
        return $user->hasRoleAtLeast(Role::Admin);
    }

    public function delete(User $user, Changelog $changelog): bool
    {
        return $user->hasRoleAtLeast(Role::Admin);
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
