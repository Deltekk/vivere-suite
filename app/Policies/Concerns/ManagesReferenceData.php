<?php

namespace App\Policies\Concerns;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Regole comuni per i dati di riferimento (edifici, corsi, aulette, scuole, cariche, ...):
 * lo staff li consulta, gli admin li gestiscono.
 *
 * La cancellazione è permessa agli admin, ma il DB la rifiuta se il dato è ancora usato
 * (FK senza cascade): le Resource mostrano un avviso invece di un errore.
 */
trait ManagesReferenceData
{
    public function viewAny(User $user): bool
    {
        return $user->hasRoleAtLeast(Role::Staff);
    }

    public function view(User $user, Model $model): bool
    {
        return $user->hasRoleAtLeast(Role::Staff);
    }

    public function create(User $user): bool
    {
        return $user->hasRoleAtLeast(Role::Admin);
    }

    public function update(User $user, Model $model): bool
    {
        return $user->hasRoleAtLeast(Role::Admin);
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->hasRoleAtLeast(Role::Admin);
    }

    /** Niente cancellazioni in blocco: un errore su un dato in uso le renderebbe confuse. */
    public function deleteAny(User $user): bool
    {
        return false;
    }
}
