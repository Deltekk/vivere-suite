<?php

namespace Modules\HumanResources\Filament\Resources\Users;

use App\Enums\CourseYear;
use App\Enums\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Modules\HumanResources\Actions\AcceptRegistration;
use Modules\HumanResources\Actions\BanUser;
use Modules\HumanResources\Actions\ChangeUserRole;
use Modules\HumanResources\Actions\ConfirmCourseYear;
use Modules\HumanResources\Actions\UnbanUser;
use Modules\HumanResources\Filament\Schemas\UserFields;
use Modules\HumanResources\Support\RegistrationWarnings;

/**
 * Azioni Filament sugli utenti, usate sia nella tabella sia nella scheda del singolo utente.
 * La logica sta nelle classi di Modules\HumanResources\Actions; qui solo l'interfaccia.
 * La visibilità di ogni azione segue UserPolicy.
 */
final class UserActions
{
    public static function accept(): Action
    {
        return Action::make('accept')
            ->label('Accetta')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (User $record): bool => Gate::allows('accept', $record))
            ->requiresConfirmation()
            ->modalHeading('Accettare la registrazione?')
            ->modalDescription(function (User $record): string {
                $warnings = RegistrationWarnings::for($record);

                return $warnings === []
                    ? 'L\'utente riceverà una mail e potrà usare la suite.'
                    : 'Attenzione: '.implode(' ', $warnings).' Accetta solo se hai verificato che è tutto in regola.';
            })
            ->action(fn (User $record) => app(AcceptRegistration::class)->handle($record, self::currentUser()))
            ->successNotificationTitle('Registrazione accettata');
    }

    /**
     * Accettazione di più registrazioni insieme (solo quelle senza avvisi, per prudenza).
     */
    public static function acceptBulk(): BulkAction
    {
        return BulkAction::make('acceptSelected')
            ->label('Accetta le selezionate')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription('Verranno accettate solo le registrazioni in attesa senza avvisi: quelle con avvisi vanno controllate una per una.')
            ->action(function (Collection $records, BulkAction $action): void {
                $accepted = 0;

                foreach ($records as $user) {
                    if (Gate::allows('accept', $user) && RegistrationWarnings::for($user) === []) {
                        app(AcceptRegistration::class)->handle($user, self::currentUser());
                        $accepted++;
                    }
                }

                $action->successNotificationTitle("Registrazioni accettate: {$accepted}");
                $action->success();
            })
            ->deselectRecordsAfterCompletion();
    }

    public static function ban(): Action
    {
        return Action::make('ban')
            ->label('Banna')
            ->icon('heroicon-o-no-symbol')
            ->color('danger')
            ->visible(fn (User $record): bool => Gate::allows('ban', $record))
            ->modalHeading('Bannare l\'utente?')
            ->modalDescription('L\'utente non potrà più accedere e riceverà per mail la motivazione.')
            ->schema([
                Textarea::make('reason')->label('Motivazione (verrà inviata all\'utente)')
                    ->required()->minLength(10)->maxLength(2000)->rows(4),
            ])
            ->action(fn (User $record, array $data) => app(BanUser::class)->handle($record, $data['reason'], self::currentUser()))
            ->successNotificationTitle('Utente bannato');
    }

    public static function unban(): Action
    {
        return Action::make('unban')
            ->label('Revoca ban')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->visible(fn (User $record): bool => Gate::allows('unban', $record))
            ->requiresConfirmation()
            ->modalDescription('L\'account tornerà attivo e l\'utente riceverà una mail. Lo storico del ban resta.')
            ->action(fn (User $record) => app(UnbanUser::class)->handle($record, self::currentUser()))
            ->successNotificationTitle('Ban revocato');
    }

    public static function changeRole(): Action
    {
        return Action::make('changeRole')
            ->label('Cambia ruolo')
            ->icon('heroicon-o-shield-check')
            ->visible(fn (User $record): bool => Gate::allows('changeRole', $record))
            ->fillForm(fn (User $record): array => ['role' => $record->role->value])
            ->schema([
                Select::make('role')->label('Ruolo')
                    ->options(collect(Role::cases())
                        ->reject(fn (Role $role): bool => $role === Role::SuperAdmin)
                        ->mapWithKeys(fn (Role $role): array => [$role->value => $role->getLabel()]))
                    ->required()
                    ->helperText('Chi diventa staff o admin dovrà inserire codice fiscale e luogo di nascita al prossimo accesso.'),
            ])
            ->action(fn (User $record, array $data) => app(ChangeUserRole::class)->handle($record, Role::from($data['role'] instanceof Role ? $data['role']->value : $data['role']), self::currentUser()))
            ->successNotificationTitle('Ruolo aggiornato');
    }

    public static function confirmYear(): Action
    {
        return Action::make('confirmYear')
            ->label('Conferma anno')
            ->icon('heroicon-o-academic-cap')
            ->color('success')
            ->visible(fn (User $record): bool => Gate::allows('confirmYear', $record))
            ->fillForm(fn (User $record): array => ['course_year' => $record->course_year, 'course_id' => $record->course_id])
            ->schema([
                Select::make('course_year')->label('Anno di corso')->options(CourseYear::class)->required()->live(),
                Select::make('course_id')->label('Corso')
                    ->relationship('course', 'name')->searchable()->preload()
                    ->hidden(fn (Get $get): bool => UserFields::courseYearFromState($get('course_year'))?->isHighSchool() === true)
                    ->required(fn (Get $get): bool => UserFields::courseYearFromState($get('course_year'))?->isHighSchool() !== true),
            ])
            ->action(fn (User $record, array $data) => app(ConfirmCourseYear::class)->handle(
                $record,
                CourseYear::from(self::enumValue($data['course_year'])),
                $data['course_id'] ?? null,
                self::currentUser(),
            ))
            ->successNotificationTitle('Anno di corso confermato');
    }

    private static function enumValue(mixed $value): string
    {
        return $value instanceof CourseYear ? $value->value : (string) $value;
    }

    private static function currentUser(): User
    {
        /** @var User */
        return Filament::auth()->user();
    }
}
