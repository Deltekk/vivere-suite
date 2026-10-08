<?php

namespace Modules\HumanResources\Filament\Resources\Macroareas;

use App\Filament\Actions\DeleteIfUnusedAction;
use App\Models\Macroarea;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\HumanResources\Filament\Resources\Macroareas\Pages\ManageMacroareas;
use UnitEnum;

/**
 * Macroaree dell'ateneo (Ingegneria, Medicina, ...), che raggruppano i dipartimenti.
 *
 * Resource "semplice": una sola pagina con tabella, creazione e modifica in finestra modale.
 * Permessi in App\Policies\MacroareaPolicy (staff consulta, admin gestisce).
 */
class MacroareaResource extends Resource
{
    protected static ?string $model = Macroarea::class;

    protected static ?string $modelLabel = 'macroarea';

    protected static ?string $pluralModelLabel = 'macroaree';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquare3Stack3d;

    protected static string|UnitEnum|null $navigationGroup = 'Ateneo';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nome')->required()->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('departments_count')->label('Dipartimenti')->counts('departments')->sortable(),
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
            'index' => ManageMacroareas::route('/'),
        ];
    }
}
