<?php

namespace Modules\HumanResources\Filament\Resources\Departments;

use App\Filament\Actions\DeleteIfUnusedAction;
use App\Models\Department;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\HumanResources\Filament\Resources\Departments\Pages\ManageDepartments;
use UnitEnum;

/**
 * Dipartimenti accademici (unità organizzative, non edifici: per quelli vedi BuildingResource).
 *
 * Resource "semplice": una sola pagina con tabella, creazione e modifica in finestra modale.
 * Permessi in App\Policies\DepartmentPolicy (staff consulta, admin gestisce).
 */
class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $modelLabel = 'dipartimento';

    protected static ?string $pluralModelLabel = 'dipartimenti';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Ateneo';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nome')->required()->maxLength(255)
                    ->placeholder('Dipartimento di Ingegneria')
                    ->unique(ignoreRecord: true),
                Select::make('macroarea_id')->label('Macroarea')
                    ->relationship('macroarea', 'name')
                    ->searchable()->preload()->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('macroarea.name')->label('Macroarea')->sortable(),
                TextColumn::make('courses_count')->label('Corsi')->counts('courses')->sortable(),
            ])
            ->filters([
                SelectFilter::make('macroarea')->label('Macroarea')->relationship('macroarea', 'name'),
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
            'index' => ManageDepartments::route('/'),
        ];
    }
}
