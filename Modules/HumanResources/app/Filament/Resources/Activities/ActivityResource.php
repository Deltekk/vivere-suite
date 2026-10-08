<?php

namespace Modules\HumanResources\Filament\Resources\Activities;

use App\Models\Activity;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\HumanResources\Filament\Resources\Activities\Pages\ListActivities;
use UnitEnum;

/**
 * Audit log di tutta la suite, in sola lettura e solo per i super admin (preambolo).
 * Le stesse voci sono anche su file: storage/logs/audit-*.log.
 */
class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $modelLabel = 'voce di audit';

    protected static ?string $pluralModelLabel = 'audit log';

    protected static ?string $slug = 'audit-log';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columns(2)->schema([
                    TextEntry::make('created_at')->label('Quando')->dateTime('d/m/Y H:i:s'),
                    TextEntry::make('log_name')->label('Modulo')->badge(),
                    TextEntry::make('description')->label('Descrizione'),
                    TextEntry::make('event')->label('Evento')->badge(),
                    TextEntry::make('causer_label')->label('Chi')
                        ->state(fn (Activity $record): string => self::causerLabel($record)),
                    TextEntry::make('subject_label')->label('Su cosa')
                        ->state(fn (Activity $record): string => $record->subject_type ? "{$record->subject_type} {$record->subject_id}" : '—'),
                ]),
                Section::make('Modifiche')->schema([
                    KeyValueEntry::make('changes_new')->label('Valori nuovi')
                        ->state(fn (Activity $record): array => self::flatten($record->attribute_changes?->get('attributes'))),
                    KeyValueEntry::make('changes_old')->label('Valori precedenti')
                        ->state(fn (Activity $record): array => self::flatten($record->attribute_changes?->get('old'))),
                    KeyValueEntry::make('properties_list')->label('Dati aggiuntivi')
                        ->state(fn (Activity $record): array => self::flatten($record->properties?->toArray())),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Quando')->dateTime('d/m/Y H:i:s')->sortable(),
                TextColumn::make('log_name')->label('Modulo')->badge(),
                TextColumn::make('event')->label('Evento')->badge(),
                TextColumn::make('description')->label('Descrizione')->searchable()->wrap(),
                TextColumn::make('causer_label')->label('Chi')->state(fn (Activity $record): string => self::causerLabel($record)),
                TextColumn::make('subject_type')->label('Su cosa')->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')->label('Modulo')
                    ->options(fn (): array => Activity::query()->distinct()->orderBy('log_name')->pluck('log_name', 'log_name')->filter()->all()),
                SelectFilter::make('event')->label('Evento')
                    ->options(fn (): array => Activity::query()->distinct()->orderBy('event')->pluck('event', 'event')->filter()->all()),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }

    private static function causerLabel(Activity $activity): string
    {
        $causer = $activity->causer;

        return $causer?->getAttribute('username') ?? ($activity->causer_type ? "{$activity->causer_type} {$activity->causer_id}" : 'Sistema');
    }

    /**
     * Valori come testo, per mostrarli in una tabella chiave/valore.
     *
     * @return array<string, string>
     */
    private static function flatten(mixed $values): array
    {
        return collect(is_array($values) ? $values : [])
            ->map(fn (mixed $value): string => is_scalar($value) || $value === null ? (string) $value : (string) json_encode($value, JSON_UNESCAPED_UNICODE))
            ->all();
    }
}
