<?php

namespace Modules\HumanResources\Filament\Resources\Aulette\RelationManagers;

use App\Enums\Role;
use App\Models\Auletta;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\HumanResources\Actions\ManageAulettaManagers;
use Modules\HumanResources\Filament\Actions\AulettaManagementActions;

/**
 * Gestori dell'auletta, con storico. Nomina e rimozione passano da ManageAulettaManagers,
 * che manda le mail per ritirare e restituire le chiavi (HR 2.2.2).
 */
class ManagersRelationManager extends RelationManager
{
    protected static string $relationship = 'managers';

    protected static ?string $title = 'Gestori';

    protected static ?string $modelLabel = 'gestore';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return (bool) Filament::auth()->user()?->hasRoleAtLeast(Role::Staff);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.username')
            ->columns([
                TextColumn::make('user.username')->label('Gestore')->searchable(),
                TextColumn::make('keys_state')->label('Chiavi')->badge(),
                TextColumn::make('assigned_at')->label('Dal')->dateTime('d/m/Y')->sortable(),
                TextColumn::make('removed_at')->label('Al')->dateTime('d/m/Y')->placeholder('In carica'),
            ])
            ->defaultSort('assigned_at', 'desc')
            ->filters([
                TernaryFilter::make('active')->label('Gestione')
                    ->trueLabel('Solo attivi')->falseLabel('Solo passati')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('removed_at'),
                        false: fn (Builder $query) => $query->whereNotNull('removed_at'),
                    )
                    ->default(true),
            ])
            ->headerActions([
                Action::make('assign')
                    ->label('Nomina gestore')
                    ->icon('heroicon-o-key')
                    ->visible(fn (): bool => (bool) Filament::auth()->user()?->hasRoleAtLeast(Role::Admin))
                    ->schema([
                        Select::make('user_id')->label('Utente (solo staff)')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => User::query()
                                ->whereIn('role', [Role::Staff, Role::Admin, Role::SuperAdmin])
                                ->where(fn (Builder $query) => $query
                                    ->where('username', 'ilike', "%{$search}%")
                                    ->orWhere('surname', 'ilike', "%{$search}%"))
                                ->limit(20)
                                ->pluck('username', 'id')
                                ->all())
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        /** @var Auletta $auletta */
                        $auletta = $this->getOwnerRecord();

                        app(ManageAulettaManagers::class)->assign(User::findOrFail((string) $data['user_id']), $auletta, Filament::auth()->user());
                    })
                    ->successNotificationTitle('Gestore nominato: gli è stata mandata la mail per ritirare le chiavi'),
            ])
            ->recordActions(AulettaManagementActions::all());
    }
}
