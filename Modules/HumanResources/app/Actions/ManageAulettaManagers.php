<?php

namespace Modules\HumanResources\Actions;

use App\Enums\KeyState;
use App\Enums\Role;
use App\Models\Auletta;
use App\Models\AulettaManager;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Modules\HumanResources\Notifications\AulettaKeysPickup;
use Modules\HumanResources\Notifications\AulettaKeysReturn;

/**
 * Nomina e rimozione dei gestori delle aulette (HR 2.2.2-2.2.4), con le mail per le chiavi.
 * Solo gli admin; possono essere gestori solo gli utenti non studenti.
 */
class ManageAulettaManagers
{
    public function assign(User $user, Auletta $auletta, User $assignedBy): AulettaManager
    {
        Gate::forUser($assignedBy)->authorize('manageAssignments', $user);

        if (! $user->hasRoleAtLeast(Role::Staff)) {
            throw new InvalidArgumentException('Solo lo staff può gestire un\'auletta.');
        }

        $management = AulettaManager::create([
            'user_id' => $user->id,
            'auletta_id' => $auletta->id,
            'keys_state' => KeyState::HandingOver,
            'assigned_at' => now(),
        ]);

        $user->notify(new AulettaKeysPickup($auletta));

        return $management;
    }

    public function remove(AulettaManager $management, User $removedBy): void
    {
        Gate::forUser($removedBy)->authorize('manageAssignments', $management->user);

        $management->removed_at = now();
        // Se le chiavi non erano ancora state consegnate non c'è niente da restituire
        $management->keys_state = $management->keys_state === KeyState::HandingOver ? KeyState::Returned : KeyState::Requested;
        $management->save();

        if ($management->keys_state === KeyState::Requested) {
            $management->user->notify(new AulettaKeysReturn($management->auletta));
        }
    }

    /**
     * Registra il passaggio delle chiavi (consegnate al gestore o restituite).
     */
    public function updateKeys(AulettaManager $management, KeyState $state, User $updatedBy): void
    {
        Gate::forUser($updatedBy)->authorize('manageAssignments', $management->user);

        $management->keys_state = $state;
        $management->save();
    }
}
