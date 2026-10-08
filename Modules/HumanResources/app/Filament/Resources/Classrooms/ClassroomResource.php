<?php

namespace Modules\HumanResources\Filament\Resources\Classrooms;

use App\Filament\Actions\DeleteIfUnusedAction;
use App\Models\Classroom;
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
use Modules\HumanResources\Filament\Resources\Classrooms\Pages\ManageClassrooms;
use UnitEnum;

/**
 * Aule dell'ateneo (codice, nome, edificio, dipartimento che le gestisce).
 *
 * Resource "semplice": una sola pagina con tabella, creazione e modifica in finestra modale.
 * Permessi in App\Policies\ClassroomPolicy (staff consulta, admin gestisce).
 */
class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;

    protected static ?string $modelLabel = 'aula';

    protected static ?string $pluralModelLabel = 'aule';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Ateneo';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')->label('Codice')->required()->maxLength(50)->placeholder('F220'),
                TextInput::make('name')->label('Nome')->maxLength(255)->placeholder('Aula Rubino'),
                Select::make('building_id')->label('Edificio')
                    ->relationship('building', 'name')
                    ->searchable()->preload()->required(),
                Select::make('department_id')->label('Dipartimento che la gestisce')
                    ->relationship('department', 'name')
                    ->searchable()->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Codice')->searchable()->sortable(),
                TextColumn::make('name')->label('Nome')->searchable(),
                TextColumn::make('building.name')->label('Edificio')->sortable(),
                TextColumn::make('department.name')->label('Dipartimento')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('building')->label('Edificio')->relationship('building', 'name')->searchable()->preload(),
            ])
            ->defaultSort('code')
            ->recordActions([
                EditAction::make(),
                DeleteIfUnusedAction::make(),
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'name'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageClassrooms::route('/'),
        ];
    }
}
