<?php

namespace Modules\HumanResources\Filament\Resources\Courses\RelationManagers;

use App\Enums\Role;
use App\Models\Course;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Facades\Filament;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Amministratori del corso (core.admin_courses). Possono esserlo solo utenti con ruolo admin,
 * anche iscritti a un altro corso (HR 2.2.4). Ricevono la mail per le nuove registrazioni.
 */
class AdministratorsRelationManager extends RelationManager
{
    protected static string $relationship = 'administrators';

    protected static ?string $title = 'Amministratori del corso';

    protected static ?string $modelLabel = 'amministratore';

    public function isReadOnly(): bool
    {
        return ! Filament::auth()->user()?->hasRoleAtLeast(Role::Admin);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('username')
            ->columns([
                TextColumn::make('username')->label('Username')->searchable(),
                TextColumn::make('name')->label('Nome')
                    ->formatStateUsing(fn (User $record): string => $record->getFilamentName()),
                TextColumn::make('course.name')->label('Iscritto a')->placeholder('—'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Aggiungi amministratore')
                    ->recordSelectSearchColumns(['username', 'name', 'surname'])
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->whereIn('role', [Role::Admin, Role::SuperAdmin]))
                    ->after(fn (Model $record) => activity('humanresources')
                        ->performedOn($this->getCourse())->causedBy(Filament::auth()->user())
                        ->event('course_admin_added')->withProperties(['user_id' => $record->getKey()])
                        ->log('Amministratore di corso aggiunto')),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Rimuovi')
                    ->after(fn (Model $record) => activity('humanresources')
                        ->performedOn($this->getCourse())->causedBy(Filament::auth()->user())
                        ->event('course_admin_removed')->withProperties(['user_id' => $record->getKey()])
                        ->log('Amministratore di corso rimosso')),
            ]);
    }

    private function getCourse(): Course
    {
        /** @var Course */
        return $this->getOwnerRecord();
    }
}
