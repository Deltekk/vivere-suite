<?php

namespace Modules\HumanResources\Filament\Resources\Users\Pages;

use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\HumanResources\Filament\Resources\Users\UserActions;
use Modules\HumanResources\Filament\Resources\Users\UserResource;

/**
 * Scheda di un utente: dati, avvisi per lo staff, azioni e (per gli admin) mandati,
 * corsi amministrati, gestione delle aulette e storico dei ban.
 */
class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            UserActions::accept(),
            UserActions::confirmYear(),
            EditAction::make(),
            ActionGroup::make([
                UserActions::changeRole(),
                UserActions::ban(),
                UserActions::unban(),
            ])->label('Altre azioni')->button(),
        ];
    }
}
