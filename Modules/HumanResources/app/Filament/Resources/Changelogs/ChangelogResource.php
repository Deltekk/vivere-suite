<?php

namespace Modules\HumanResources\Filament\Resources\Changelogs;

use App\Models\Changelog;
use App\Support\Suite;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\HumanResources\Filament\Resources\Changelogs\Pages\ManageChangelogs;
use UnitEnum;

/**
 * Novità delle piattaforme (preambolo): ogni voce viene mostrata una volta a ogni utente,
 * al primo accesso utile alla piattaforma a cui si riferisce, dalla data di pubblicazione.
 */
class ChangelogResource extends Resource
{
    protected static ?string $model = Changelog::class;

    protected static ?string $modelLabel = 'novità';

    protected static ?string $pluralModelLabel = 'novità (changelog)';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Comunicazioni';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service')->label('Piattaforma')->options(Suite::panelServices())->required(),
                DateTimePicker::make('published_at')->label('Pubblica dal')->required()->default(now())
                    ->native(false)->seconds(false)
                    ->helperText('Puoi prepararla prima del rilascio: gli utenti la vedranno da questa data.'),
                TextInput::make('title')->label('Titolo')->required()->maxLength(150)->columnSpanFull(),
                MarkdownEditor::make('body')->label('Testo')->required()->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Titolo')->searchable(),
                TextColumn::make('service')->label('Piattaforma')
                    ->formatStateUsing(fn (string $state): string => Suite::panelServices()[$state] ?? $state),
                TextColumn::make('published_at')->label('Pubblicata dal')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('viewers_count')->label('Viste')->counts('viewers'),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('service')->label('Piattaforma')->options(Suite::panelServices()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageChangelogs::route('/'),
        ];
    }
}
