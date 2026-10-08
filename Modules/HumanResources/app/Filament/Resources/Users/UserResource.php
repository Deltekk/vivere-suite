<?php

namespace Modules\HumanResources\Filament\Resources\Users;

use App\Enums\CourseYear;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\StaffProfile;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Modules\HumanResources\Filament\Resources\Users\Pages\CreateUser;
use Modules\HumanResources\Filament\Resources\Users\Pages\EditUser;
use Modules\HumanResources\Filament\Resources\Users\Pages\ListUsers;
use Modules\HumanResources\Filament\Resources\Users\Pages\ViewUser;
use Modules\HumanResources\Filament\Resources\Users\RelationManagers\AcademicRolesRelationManager;
use Modules\HumanResources\Filament\Resources\Users\RelationManagers\AdministeredCoursesRelationManager;
use Modules\HumanResources\Filament\Resources\Users\RelationManagers\AulettaManagementsRelationManager;
use Modules\HumanResources\Filament\Resources\Users\RelationManagers\BansRelationManager;
use Modules\HumanResources\Filament\Schemas\UserFields;
use Modules\HumanResources\Support\RegistrationWarnings;
use UnitEnum;

/**
 * Gestione degli utenti (HR 2.2.3-2.2.4): lo staff vede gli utenti e accetta le registrazioni,
 * gli admin li inseriscono, modificano, bannano, cambiano ruolo e gestiscono mandati,
 * corsi amministrati e aulette. Permessi in App\Policies\UserPolicy.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'utente';

    protected static ?string $pluralModelLabel = 'utenti';

    protected static ?string $slug = 'utenti';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Persone';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'username';

    /**
     * Numero di registrazioni in attesa, mostrato accanto alla voce di menu.
     */
    public static function getNavigationBadge(): ?string
    {
        $pending = User::query()->where('status', UserStatus::Pending)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['username', 'name', 'surname', 'email'];
    }

    /**
     * Form degli admin (creazione e modifica). Ruolo e stato non sono qui: si cambiano con le
     * azioni dedicate, che controllano i permessi e mandano le mail.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Anagrafica')->columns(2)->schema([
                    UserFields::name(),
                    UserFields::surname(),
                    UserFields::birthday(),
                    UserFields::gender(),
                    UserFields::phone(),
                ]),
                Section::make('Account')->columns(2)->schema([
                    UserFields::email(),
                    UserFields::username(),
                ]),
                Section::make('Studi')->columns(2)->schema([
                    UserFields::courseYear(),
                    UserFields::course(),
                    UserFields::school(),
                ]),
                Section::make('Social')->columns(2)->collapsed()->schema([
                    UserFields::telegram(),
                    UserFields::instagram(),
                ]),
                Section::make('Profilo staff')
                    ->description('Dati richiesti solo a staff e amministratori.')
                    ->relationship('staffProfile')
                    ->visible(fn (?User $record): bool => $record?->hasRoleAtLeast(Role::Staff) ?? false)
                    ->columns(2)
                    ->schema([
                        TextInput::make('tax_code')->label('Codice fiscale')->required()->length(16)
                            ->dehydrateStateUsing(fn (?string $state): ?string => $state === null ? null : strtoupper($state))
                            ->unique(StaffProfile::class, 'tax_code', ignoreRecord: true),
                        TextInput::make('birth_city')->label('Città di nascita')->required(),
                        TextInput::make('birth_province')->label('Provincia di nascita')->required(),
                        TextInput::make('birth_country')->label('Stato di nascita')->required()->default('Italia'),
                    ]),
            ]);
    }

    /**
     * Scheda del singolo utente. I dati riservati (telefono, profilo staff) solo agli admin.
     */
    public static function infolist(Schema $schema): Schema
    {
        $canSeePrivateData = fn (User $record): bool => Gate::allows('viewPrivateData', $record);

        return $schema
            ->components([
                Section::make('Avvisi per lo staff')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('warning')
                    ->visible(fn (User $record): bool => RegistrationWarnings::for($record) !== [])
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('warnings')->hiddenLabel()->bulleted()->listWithLineBreaks()
                            ->state(fn (User $record): array => RegistrationWarnings::for($record)),
                    ]),
                Section::make('Anagrafica')->columns(2)->schema([
                    TextEntry::make('name')->label('Nome'),
                    TextEntry::make('surname')->label('Cognome'),
                    TextEntry::make('username')->label('Username'),
                    TextEntry::make('email')->label('Email')->copyable(),
                    TextEntry::make('birthday')->label('Data di nascita')->date('d/m/Y'),
                    TextEntry::make('gender')->label('Genere')->placeholder('Non indicato'),
                    TextEntry::make('phone_number')->label('Telefono')->visible($canSeePrivateData),
                ]),
                Section::make('Stato')->columns(2)->schema([
                    TextEntry::make('role')->label('Ruolo')->badge(),
                    TextEntry::make('status')->label('Stato')->badge(),
                    TextEntry::make('email_verified_at')->label('Mail verificata il')->dateTime('d/m/Y H:i')->placeholder('Non ancora verificata'),
                    TextEntry::make('created_at')->label('Registrato il')->dateTime('d/m/Y H:i'),
                ]),
                Section::make('Studi')->columns(2)->schema([
                    TextEntry::make('course_year')->label('Anno di corso'),
                    TextEntry::make('course.name')->label('Corso')->placeholder('—'),
                    TextEntry::make('school.name')->label('Scuola di provenienza')->placeholder('—'),
                    TextEntry::make('course_year_confirmed_at')->label('Anno confermato il')->dateTime('d/m/Y')->placeholder('Mai'),
                ]),
                Section::make('Social')->columns(2)->schema([
                    TextEntry::make('telegram_tag')->label('Telegram')->prefix('@')->placeholder('—'),
                    TextEntry::make('instagram_tag')->label('Instagram')->prefix('@')->placeholder('—'),
                ]),
                Section::make('Profilo staff')->columns(2)
                    ->visible(fn (User $record): bool => $canSeePrivateData($record) && $record->staffProfile !== null)
                    ->schema([
                        TextEntry::make('staffProfile.tax_code')->label('Codice fiscale'),
                        TextEntry::make('staffProfile.birth_city')->label('Città di nascita'),
                        TextEntry::make('staffProfile.birth_province')->label('Provincia di nascita'),
                        TextEntry::make('staffProfile.birth_country')->label('Stato di nascita'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('surname')->label('Nome')
                    ->formatStateUsing(fn (User $record): string => $record->getFilamentName())
                    ->searchable(['name', 'surname'])
                    ->sortable(),
                TextColumn::make('username')->label('Username')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('warnings')->label('Avvisi')
                    ->state(fn (User $record): bool => RegistrationWarnings::for($record) !== [])
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')->trueColor('warning')
                    ->falseIcon('')
                    ->tooltip(fn (User $record): ?string => implode("\n", RegistrationWarnings::for($record)) ?: null),
                TextColumn::make('role')->label('Ruolo')->badge()->sortable(),
                TextColumn::make('status')->label('Stato')->badge()->sortable(),
                TextColumn::make('course.name')->label('Corso')->placeholder('—')->toggleable(),
                TextColumn::make('course_year')->label('Anno')->toggleable(),
                TextColumn::make('created_at')->label('Registrato il')->dateTime('d/m/Y')->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')->label('Ruolo')->options(Role::class),
                SelectFilter::make('course')->label('Corso')->relationship('course', 'name')->searchable()->preload(),
                SelectFilter::make('course_year')->label('Anno di corso')->options(CourseYear::class),
            ])
            ->recordActions([
                UserActions::accept(),
                ViewAction::make(),
                ActionGroup::make([
                    EditAction::make(),
                    UserActions::confirmYear(),
                    UserActions::changeRole(),
                    UserActions::ban(),
                    UserActions::unban(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    UserActions::acceptBulk(),
                ])->visible(fn (): bool => (bool) Filament::auth()->user()?->hasRoleAtLeast(Role::Staff)),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AcademicRolesRelationManager::class,
            AdministeredCoursesRelationManager::class,
            AulettaManagementsRelationManager::class,
            BansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
