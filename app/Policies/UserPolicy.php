<?php

namespace App\Policies;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;

/**
 * Permessi sugli utenti (documento HR 2.2.3-2.2.5 e decisioni in CLAUDE.md).
 *
 * - Staff: vede gli utenti e accetta le registrazioni (D3).
 * - Admin: crea, modifica, banna, cambia i ruoli, gestisce mandati, corsi e aulette.
 * - Super admin: come l'admin, ma non può essere modificato, bannato o declassato da nessuno.
 * - Nessuno cancella un utente: chi si disiscrive viene anonimizzato.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRoleAtLeast(Role::Staff);
    }

    public function view(User $user, User $model): bool
    {
        return $user->is($model) || $user->hasRoleAtLeast(Role::Staff);
    }

    public function create(User $user): bool
    {
        return $user->hasRoleAtLeast(Role::Admin);
    }

    /**
     * Modifica dei dati anagrafici. Il super admin lo può modificare solo un super admin.
     */
    public function update(User $user, User $model): bool
    {
        if ($model->role === Role::SuperAdmin) {
            return $user->role === Role::SuperAdmin;
        }

        return $user->hasRoleAtLeast(Role::Admin);
    }

    public function delete(User $user, User $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Accettare una registrazione in attesa (qualsiasi staffer, D3).
     */
    public function accept(User $user, User $model): bool
    {
        return $model->status === UserStatus::Pending && $user->hasRoleAtLeast(Role::Staff);
    }

    /**
     * Confermare l'anno di corso di chi è "da confermare" dopo il passaggio d'anno.
     */
    public function confirmYear(User $user, User $model): bool
    {
        return $model->status === UserStatus::ToConfirm && $user->hasRoleAtLeast(Role::Admin);
    }

    /**
     * Bannare: solo admin, mai sé stessi né un super admin.
     */
    public function ban(User $user, User $model): bool
    {
        return $model->status !== UserStatus::Banned
            && $model->role !== Role::SuperAdmin
            && ! $user->is($model)
            && $user->hasRoleAtLeast(Role::Admin);
    }

    /**
     * Riattivare un utente bannato.
     */
    public function unban(User $user, User $model): bool
    {
        return $model->status === UserStatus::Banned && $user->hasRoleAtLeast(Role::Admin);
    }

    /**
     * Cambiare il ruolo: solo admin, mai il proprio né quello di un super admin.
     * Il ruolo SuperAdmin non si assegna dall'interfaccia (lo crea SuperAdminSeeder).
     */
    public function changeRole(User $user, User $model): bool
    {
        return $model->role !== Role::SuperAdmin
            && ! $user->is($model)
            && $user->hasRoleAtLeast(Role::Admin);
    }

    /**
     * Gestire mandati istituzionali, corsi amministrati e gestioni delle aulette.
     */
    public function manageAssignments(User $user, User $model): bool
    {
        return $user->hasRoleAtLeast(Role::Admin);
    }

    /**
     * Vedere i dati riservati (profilo staff: codice fiscale, luogo di nascita).
     */
    public function viewPrivateData(User $user, User $model): bool
    {
        return $user->is($model) || $user->hasRoleAtLeast(Role::Admin);
    }

    /**
     * Inviare notifiche a molti utenti insieme.
     */
    public function broadcast(User $user): bool
    {
        return $user->hasRoleAtLeast(Role::Admin);
    }
}
