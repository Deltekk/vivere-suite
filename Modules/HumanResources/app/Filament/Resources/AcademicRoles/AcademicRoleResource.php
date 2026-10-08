<?php

namespace Modules\HumanResources\Filament\Resources\AcademicRoles;

use App\Filament\Actions\DeleteIfUnusedAction;
use App\Models\AcademicRole;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\HumanResources\Filament\Resources\AcademicRoles\Pages\ManageAcademicRoles;
use UnitEnum;

/**
 * Cariche istituzionali (CDD, CCS, CDA, ...). I mandati si assegnano dalla scheda dell'utente.
 *
 * Resource "semplice": una sola pagina con tabella, creazione e modifica in finestra modale.
 * Permessi in App\Policies\AcademicRolePolicy (staff consulta, admin gestisce).
 */
class AcademicRoleResource extends Resource
{
    protected static ?string $model = AcademicRole::class;

    protected static ?string $modelLabel = 'carica istituzionale';

    protected static ?string $pluralModelLabel = 'cariche istituzionali';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Associazione';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'role';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('role')->label('Carica')->required()->maxLength(50)->placeholder('CDD')
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('role')->label('Carica')->searchable()->sortable(),
                TextColumn::make('user_academic_roles_count')->label('Mandati')->counts('userAcademicRoles'),
            ])
            ->defaultSort('role')
            ->recordActions([
                EditAction::make(),
                DeleteIfUnusedAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAcademicRoles::route('/'),
        ];
    }
}
