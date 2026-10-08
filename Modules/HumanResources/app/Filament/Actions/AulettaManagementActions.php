<?php

namespace Modules\HumanResources\Filament\Actions;

use App\Enums\KeyState;
use App\Models\AulettaManager;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Modules\HumanResources\Actions\ManageAulettaManagers;

/**
 * Azioni sulle gestioni delle aulette (chiavi consegnate/restituite, rimozione del gestore),
 * condivise dalla scheda dell'auletta e da quella dell'utente.
 */
final class AulettaManagementActions
{
    /**
     * @return list<Action>
     */
    public static function all(): array
    {
        return [self::keysHandedOver(), self::keysReturned(), self::remove()];
    }

    public static function keysHandedOver(): Action
    {
        return Action::make('keysHandedOver')
            ->label('Chiavi consegnate')
            ->icon('heroicon-o-key')
            ->visible(fn (AulettaManager $record): bool => $record->keys_state === KeyState::HandingOver && self::canManage($record))
            ->action(fn (AulettaManager $record) => app(ManageAulettaManagers::class)->updateKeys($record, KeyState::HandedOver, self::currentUser()));
    }

    public static function keysReturned(): Action
    {
        return Action::make('keysReturned')
            ->label('Chiavi restituite')
            ->icon('heroicon-o-key')
            ->visible(fn (AulettaManager $record): bool => $record->keys_state === KeyState::Requested && self::canManage($record))
            ->action(fn (AulettaManager $record) => app(ManageAulettaManagers::class)->updateKeys($record, KeyState::Returned, self::currentUser()));
    }

    public static function remove(): Action
    {
        return Action::make('removeManager')
            ->label('Rimuovi')
            ->icon('heroicon-o-user-minus')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Rimuovere il gestore?')
            ->modalDescription('Se aveva già le chiavi, gli arriverà una mail per restituirle.')
            ->visible(fn (AulettaManager $record): bool => $record->removed_at === null && self::canManage($record))
            ->action(fn (AulettaManager $record) => app(ManageAulettaManagers::class)->remove($record, self::currentUser()))
            ->successNotificationTitle('Gestore rimosso');
    }

    private static function canManage(AulettaManager $management): bool
    {
        return Gate::allows('manageAssignments', $management->user);
    }

    private static function currentUser(): User
    {
        /** @var User */
        return Filament::auth()->user();
    }
}
