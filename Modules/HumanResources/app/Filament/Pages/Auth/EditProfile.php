<?php

namespace Modules\HumanResources\Filament\Pages\Auth;

use App\Models\EmailPreference;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Modules\HumanResources\Actions\AnonymizeUser;
use Modules\HumanResources\Filament\Schemas\UserFields;
use SensitiveParameter;

/**
 * Profilo dell'utente (HR 2.2.2).
 *
 * L'utente può cambiare i propri contatti, lo username, le preferenze email e la password.
 * Il cambio di mail va confermato dal nuovo indirizzo (emailChangeVerification nel panel).
 * Nome, cognome, data di nascita e studi li corregge lo staff (o la conferma di ottobre).
 * Da qui ci si può anche disiscrivere: l'account viene anonimizzato.
 */
class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('I tuoi dati')
                    ->description('Per correggere nome, cognome, data di nascita o studi scrivi allo staff.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('full_name')->label('Nome')->state(fn (): string => $this->user()->getFilamentName()),
                        TextEntry::make('birthday_display')->label('Data di nascita')->state(fn (): string => $this->user()->birthday->format('d/m/Y')),
                        TextEntry::make('course_display')->label('Studi')
                            ->state(fn (): string => trim($this->user()->course_year->getLabel().' '.($this->user()->course->name ?? ''))),
                        TextEntry::make('role_display')->label('Ruolo')->state(fn (): string => $this->user()->role->getLabel()),
                    ]),
                Section::make('Account')->columns(2)->schema([
                    UserFields::username(),
                    UserFields::email()->helperText('Se la cambi, ti mandiamo una mail al nuovo indirizzo per confermarla.'),
                ]),
                Section::make('Contatti')->columns(2)->schema([
                    UserFields::phone(),
                    UserFields::gender(),
                    UserFields::telegram(),
                    UserFields::instagram(),
                    UserFields::school(),
                ]),
                Section::make('Email')->schema([
                    CheckboxList::make('email_services')
                        ->label('Per quali servizi vuoi ricevere email?')
                        ->helperText('Le notifiche arrivano comunque in piattaforma. Le email sul tuo account (sicurezza, accettazione, ...) arrivano sempre.')
                        ->options(config('vivere.services'))
                        ->columns(2)
                        ->bulkToggleable(),
                ]),
                Section::make('Password')->description('Lascia vuoto per non cambiarla.')->columns(2)->schema([
                    $this->getPasswordFormComponent(),
                    $this->getPasswordConfirmationFormComponent(),
                    $this->getCurrentPasswordFormComponent(),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $disabled = $this->user()->emailPreferences()->where('enabled', false)->pluck('service')->all();
        $data['email_services'] = array_values(array_diff(array_keys(config('vivere.services')), $disabled));

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, #[SensitiveParameter] array $data): Model
    {
        $services = $data['email_services'] ?? [];
        unset($data['email_services']);

        foreach (array_keys(config('vivere.services')) as $service) {
            EmailPreference::updateOrCreate(
                ['user_id' => $record->getKey(), 'service' => $service],
                ['enabled' => in_array($service, $services, true)],
            );
        }

        return parent::handleRecordUpdate($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('unsubscribe')
                ->label('Elimina il mio account')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Vuoi davvero eliminare il tuo account?')
                ->modalDescription('I tuoi dati personali verranno cancellati e non potrai più accedere. L\'operazione non si può annullare.')
                ->schema([
                    TextInput::make('confirmation')->label('Per confermare scrivi ELIMINA')->required()->in(['ELIMINA']),
                ])
                ->action(function (): void {
                    $user = $this->user();

                    app(AnonymizeUser::class)->handle($user);

                    Filament::auth()->logout();
                    session()->invalidate();
                    session()->regenerateToken();

                    Notification::make()->title('Il tuo account è stato eliminato')->success()->send();

                    $this->redirect(Filament::getPanel('hr')->getLoginUrl() ?? '/');
                }),
        ];
    }

    private function user(): User
    {
        /** @var User */
        return $this->getUser();
    }
}
