<?php

namespace Modules\HumanResources\Filament\Resources\Users\RelationManagers;

use App\Models\User;
use App\Models\UserAcademicRole;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Mandati istituzionali dell'utente (HR 2.2.4 e 2.2.6): carica, inizio e decadenza.
 * I mandati scaduti restano come storico ("archiviati").
 */
class AcademicRolesRelationManager extends RelationManager
{
    protected static string $relationship = 'userAcademicRoles';

    protected static ?string $title = 'Cariche istituzionali';

    protected static ?string $modelLabel = 'mandato';

    protected static ?string $pluralModelLabel = 'mandati';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof User && Gate::allows('manageAssignments', $ownerRecord);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('academic_role_id')->label('Carica')
                    ->relationship('academicRole', 'role')
                    ->preload()->required(),
                DatePicker::make('started_at')->label('Inizio mandato')->required()->native(false)->displayFormat('d/m/Y'),
                DatePicker::make('expires_at')->label('Decadenza')->required()->native(false)->displayFormat('d/m/Y')
                    ->after('started_at'),
            ]);
    }

    public function table(Table $table): Table
    {
        $today = now(config('vivere.display_timezone'))->startOfDay();

        return $table
            ->columns([
                TextColumn::make('academicRole.role')->label('Carica')->badge(),
                TextColumn::make('started_at')->label('Dal')->date('d/m/Y')->sortable(),
                TextColumn::make('expires_at')->label('Al')->date('d/m/Y')->sortable(),
                TextColumn::make('state')->label('Stato')
                    ->state(fn (UserAcademicRole $record): string => $record->expires_at->lessThan($today)
                        ? 'Scaduto'
                        : ($record->started_at->greaterThan($today) ? 'Futuro' : 'In carica'))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'In carica' ? 'success' : 'gray'),
            ])
            ->defaultSort('expires_at', 'desc')
            ->headerActions([
                CreateAction::make()->label('Aggiungi mandato'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->modalDescription('Elimina solo i mandati inseriti per errore: quelli scaduti restano come storico.'),
            ]);
    }
}
