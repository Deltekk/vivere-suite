<?php

namespace Modules\HumanResources\Filament\Resources\Users\RelationManagers;

use App\Models\User;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Storico dei ban dell'utente (sola lettura: i ban non si modificano né si cancellano).
 */
class BansRelationManager extends RelationManager
{
    protected static string $relationship = 'bans';

    protected static ?string $title = 'Storico ban';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof User && Gate::allows('manageAssignments', $ownerRecord);
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('reason')->label('Motivazione')->wrap(),
                TextColumn::make('bannedBy.username')->label('Bannato da'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
