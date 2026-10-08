<?php

namespace Modules\HumanResources\Filament\Resources\Schools;

use App\Filament\Actions\DeleteIfUnusedAction;
use App\Models\School;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\HumanResources\Filament\Resources\Schools\Pages\ManageSchools;
use UnitEnum;

/**
 * Scuole superiori di provenienza (facoltative per gli studenti, obbligatorie per lo staff).
 *
 * Resource "semplice": una sola pagina con tabella, creazione e modifica in finestra modale.
 * Permessi in App\Policies\SchoolPolicy (staff consulta, admin gestisce).
 */
class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static ?string $modelLabel = 'scuola';

    protected static ?string $pluralModelLabel = 'scuole';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Ateneo';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nome')->required()->maxLength(255)->placeholder('Liceo Scientifico Galileo Galilei'),
                TextInput::make('city')->label('Città')->required()->maxLength(255),
                Grid::make(2)->schema([
                    TextInput::make('lat')->label('Latitudine')->numeric()->minValue(-90)->maxValue(90),
                    TextInput::make('lon')->label('Longitudine')->numeric()->minValue(-180)->maxValue(180),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('city')->label('Città')->searchable()->sortable(),
                TextColumn::make('users_count')->label('Utenti')->counts('users')->sortable(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteIfUnusedAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSchools::route('/'),
        ];
    }
}
