<?php

namespace Modules\HumanResources\Filament\Resources\Aulette;

use App\Filament\Actions\DeleteIfUnusedAction;
use App\Models\Auletta;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\HumanResources\Filament\Resources\Aulette\Pages\CreateAuletta;
use Modules\HumanResources\Filament\Resources\Aulette\Pages\EditAuletta;
use Modules\HumanResources\Filament\Resources\Aulette\Pages\ListAulette;
use Modules\HumanResources\Filament\Resources\Aulette\RelationManagers\CoursesRelationManager;
use Modules\HumanResources\Filament\Resources\Aulette\RelationManagers\ManagersRelationManager;
use UnitEnum;

/**
 * Aulette dell'associazione, con i loro gestori (nomina/rimozione con mail per le chiavi)
 * e i corsi che vi afferiscono. Permessi in App\Policies\AulettaPolicy.
 */
class AulettaResource extends Resource
{
    protected static ?string $model = Auletta::class;

    protected static ?string $modelLabel = 'auletta';

    protected static ?string $pluralModelLabel = 'aulette';

    protected static ?string $slug = 'aulette';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static string|UnitEnum|null $navigationGroup = 'Associazione';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Auletta')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nome')->required()->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('building_id')->label('Edificio')
                            ->relationship('building', 'name')
                            ->searchable()->preload()->required(),
                        Grid::make(2)->schema([
                            TextInput::make('lat')->label('Latitudine')->numeric()->minValue(-90)->maxValue(90),
                            TextInput::make('lon')->label('Longitudine')->numeric()->minValue(-180)->maxValue(180),
                        ])->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'managers as active_managers_count' => fn (Builder $managers) => $managers->whereNull('removed_at'),
            ]))
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('building.name')->label('Edificio')->sortable(),
                TextColumn::make('active_managers_count')->label('Gestori attivi')->sortable(),
                TextColumn::make('courses_count')->label('Corsi afferenti')->counts('courses'),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteIfUnusedAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ManagersRelationManager::class,
            CoursesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAulette::route('/'),
            'create' => CreateAuletta::route('/create'),
            'edit' => EditAuletta::route('/{record}/edit'),
        ];
    }
}
