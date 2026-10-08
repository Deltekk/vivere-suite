<?php

namespace Modules\HumanResources\Filament\Resources\Users\RelationManagers;

use App\Enums\Role;
use App\Models\Auletta;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Modules\HumanResources\Actions\ManageAulettaManagers;
use Modules\HumanResources\Filament\Actions\AulettaManagementActions;

/**
 * Aulette gestite dall'utente, con storico. Solo gli utenti non studenti possono gestirne una.
 */
class AulettaManagementsRelationManager extends RelationManager
{
    protected static string $relationship = 'aulettaManagements';

    protected static ?string $title = 'Gestione aulette';

    protected static ?string $modelLabel = 'gestione';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof User
            && $ownerRecord->hasRoleAtLeast(Role::Staff)
            && Gate::allows('manageAssignments', $ownerRecord);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('auletta.name')->label('Auletta'),
                TextColumn::make('keys_state')->label('Chiavi')->badge(),
                TextColumn::make('assigned_at')->label('Dal')->dateTime('d/m/Y')->sortable(),
                TextColumn::make('removed_at')->label('Al')->dateTime('d/m/Y')->placeholder('In carica'),
            ])
            ->defaultSort('assigned_at', 'desc')
            ->headerActions([
                Action::make('assign')
                    ->label('Nomina gestore di un\'auletta')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Select::make('auletta_id')->label('Auletta')
                            ->options(fn (): array => Auletta::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()->required(),
                    ])
                    ->action(function (array $data): void {
                        /** @var User $user */
                        $user = $this->getOwnerRecord();

                        app(ManageAulettaManagers::class)->assign($user, Auletta::findOrFail((string) $data['auletta_id']), Filament::auth()->user());
                    })
                    ->successNotificationTitle('Gestore nominato: gli è stata mandata la mail per ritirare le chiavi'),
            ])
            ->recordActions(AulettaManagementActions::all());
    }
}
