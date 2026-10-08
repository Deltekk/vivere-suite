<?php

namespace Modules\HumanResources\Filament\Resources\Courses;

use App\Filament\Actions\DeleteIfUnusedAction;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\HumanResources\Filament\Resources\Courses\Pages\CreateCourse;
use Modules\HumanResources\Filament\Resources\Courses\Pages\EditCourse;
use Modules\HumanResources\Filament\Resources\Courses\Pages\ListCourses;
use Modules\HumanResources\Filament\Resources\Courses\RelationManagers\AdministratorsRelationManager;
use Modules\HumanResources\Filament\Resources\Courses\RelationManagers\ClassroomsRelationManager;
use UnitEnum;

/**
 * Corsi di studio, con i loro amministratori (almeno uno per corso, HR 2.2.4), l'auletta di
 * afferenza e le aule. Permessi in App\Policies\CoursePolicy.
 */
class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $modelLabel = 'corso';

    protected static ?string $pluralModelLabel = 'corsi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Ateneo';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Corso')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nome')->required()->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                        Select::make('department_id')->label('Dipartimento')
                            ->relationship('department', 'name')
                            ->searchable()->preload()->required(),
                        Select::make('auletta_id')->label('Auletta di afferenza')
                            ->relationship('auletta', 'name')
                            ->searchable()->preload()
                            ->helperText('Decide chi vede i resoconti Kaffettino dell\'auletta e dove si gestiscono gli oggetti smarriti.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('department.name')->label('Dipartimento')->sortable()->toggleable(),
                TextColumn::make('auletta.name')->label('Auletta')->placeholder('—')->toggleable(),
                TextColumn::make('administrators_count')->label('Admin')->counts('administrators')->sortable()
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : 'success'),
                TextColumn::make('users_count')->label('Iscritti')->counts('users')->sortable(),
            ])
            ->filters([
                SelectFilter::make('department')->label('Dipartimento')->relationship('department', 'name')->searchable()->preload(),
                TernaryFilter::make('without_admins')->label('Admin')
                    ->trueLabel('Solo corsi senza admin')->falseLabel('Solo corsi con admin')
                    ->queries(
                        true: fn (Builder $query) => $query->doesntHave('administrators'),
                        false: fn (Builder $query) => $query->has('administrators'),
                    ),
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
            AdministratorsRelationManager::class,
            ClassroomsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'edit' => EditCourse::route('/{record}/edit'),
        ];
    }
}
