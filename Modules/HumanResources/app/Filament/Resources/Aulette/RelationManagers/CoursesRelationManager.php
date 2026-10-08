<?php

namespace Modules\HumanResources\Filament\Resources\Aulette\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Corsi che afferiscono all'auletta (sola lettura: l'afferenza si sceglie nella scheda del corso).
 */
class CoursesRelationManager extends RelationManager
{
    protected static string $relationship = 'courses';

    protected static ?string $title = 'Corsi afferenti';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Corso')->searchable(),
                TextColumn::make('department.name')->label('Dipartimento'),
            ]);
    }
}
