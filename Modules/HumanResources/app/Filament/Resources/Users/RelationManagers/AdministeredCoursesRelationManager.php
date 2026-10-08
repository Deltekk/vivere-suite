<?php

namespace Modules\HumanResources\Filament\Resources\Users\RelationManagers;

use App\Enums\Role;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Facades\Filament;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Corsi di cui l'utente è amministratore (core.admin_courses): solo per chi ha ruolo admin.
 */
class AdministeredCoursesRelationManager extends RelationManager
{
    protected static string $relationship = 'administeredCourses';

    protected static ?string $title = 'Corsi amministrati';

    protected static ?string $modelLabel = 'corso';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof User
            && $ownerRecord->hasRoleAtLeast(Role::Admin)
            && Gate::allows('manageAssignments', $ownerRecord);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Corso')->searchable(),
                TextColumn::make('department.name')->label('Dipartimento'),
            ])
            ->headerActions([
                AttachAction::make()->label('Aggiungi corso')->preloadRecordSelect()
                    ->after(fn (Model $record) => activity('humanresources')
                        ->performedOn($this->getOwnerRecord())->causedBy(Filament::auth()->user())
                        ->event('course_admin_added')->withProperties(['course_id' => $record->getKey()])
                        ->log('Amministratore di corso aggiunto')),
            ])
            ->recordActions([
                DetachAction::make()->label('Rimuovi')
                    ->after(fn (Model $record) => activity('humanresources')
                        ->performedOn($this->getOwnerRecord())->causedBy(Filament::auth()->user())
                        ->event('course_admin_removed')->withProperties(['course_id' => $record->getKey()])
                        ->log('Amministratore di corso rimosso')),
            ]);
    }
}
