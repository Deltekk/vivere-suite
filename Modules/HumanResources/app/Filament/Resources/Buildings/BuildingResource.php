<?php

namespace Modules\HumanResources\Filament\Resources\Buildings;

use App\Filament\Actions\DeleteIfUnusedAction;
use App\Models\Building;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\HumanResources\Filament\Resources\Buildings\Pages\ManageBuildings;
use UnitEnum;

/**
 * Edifici fisici dell'ateneo (es. "Edificio 8"), che ospitano aule e aulette.
 *
 * Resource "semplice": una sola pagina con tabella, creazione e modifica in finestra modale.
 * Permessi in App\Policies\BuildingPolicy (staff consulta, admin gestisce).
 */
class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $modelLabel = 'edificio';

    protected static ?string $pluralModelLabel = 'edifici';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Ateneo';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nome')->required()->maxLength(255)->placeholder('Edificio 8'),
                TextInput::make('number')->label('Numero')->integer()->minValue(0)
                    ->helperText('Vuoto per le sedi senza numero.')
                    ->unique(ignoreRecord: true),
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
                TextColumn::make('number')->label('Numero')->sortable(),
                TextColumn::make('aulette_count')->label('Aulette')->counts('aulette'),
                TextColumn::make('classrooms_count')->label('Aule')->counts('classrooms'),
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
            'index' => ManageBuildings::route('/'),
        ];
    }
}
