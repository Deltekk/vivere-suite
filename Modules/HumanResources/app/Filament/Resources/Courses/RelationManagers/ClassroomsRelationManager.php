<?php

namespace Modules\HumanResources\Filament\Resources\Courses\RelationManagers;

use App\Enums\Role;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Facades\Filament;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Aule in cui si tengono le lezioni del corso (core.classroom_course).
 */
class ClassroomsRelationManager extends RelationManager
{
    protected static string $relationship = 'classrooms';

    protected static ?string $title = 'Aule del corso';

    protected static ?string $modelLabel = 'aula';

    public function isReadOnly(): bool
    {
        return ! Filament::auth()->user()?->hasRoleAtLeast(Role::Admin);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('code')->label('Codice')->searchable(),
                TextColumn::make('name')->label('Nome'),
                TextColumn::make('building.name')->label('Edificio'),
            ])
            ->headerActions([
                AttachAction::make()->label('Aggiungi aula')->recordSelectSearchColumns(['code', 'name'])->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make()->label('Rimuovi'),
            ]);
    }
}
