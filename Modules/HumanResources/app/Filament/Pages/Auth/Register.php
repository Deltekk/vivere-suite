<?php

namespace Modules\HumanResources\Filament\Pages\Auth;

use App\Models\EmailPreference;
use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Modules\HumanResources\Filament\Schemas\UserFields;
use Modules\HumanResources\Support\UnipaIdentity;
use SensitiveParameter;

/**
 * Registrazione alla suite (HR 2.2.1).
 *
 * Raccoglie i dati del documento, le preferenze email e l'accettazione dell'informativa.
 * L'account nasce "in attesa" (default del DB): finché uno staffer non lo accetta, l'utente vede
 * solo la schermata di attesa. Filament manda la mail di verifica; il listener
 * NotifyRegistrationReviewers avvisa gli admin del corso.
 */
class Register extends BaseRegister
{
    protected Width|string|null $maxWidth = Width::ThreeExtraLarge;

    public function form(Schema $schema): Schema
    {
        return $schema
            // Serve ai campi collegati a una relazione (corso, scuola): l'utente non esiste ancora
            ->model(User::class)
            ->components([
                Section::make('Chi sei')->columns(2)->schema([
                    UserFields::name()->live(onBlur: true)->afterStateUpdated(fn (Get $get, Set $set) => self::suggestUsername($get, $set)),
                    UserFields::surname()->live(onBlur: true)->afterStateUpdated(fn (Get $get, Set $set) => self::suggestUsername($get, $set)),
                    UserFields::birthday(),
                    UserFields::gender(),
                    UserFields::phone(),
                ]),
                Section::make('Studi')->columns(2)->schema([
                    UserFields::courseYear(),
                    UserFields::course(),
                    UserFields::school(),
                ]),
                Section::make('Account')->columns(2)->schema([
                    UserFields::email(),
                    UserFields::username(),
                    $this->getPasswordFormComponent(),
                    $this->getPasswordConfirmationFormComponent(),
                ]),
                Section::make('Social')->description('Facoltativi: servono allo staff per contattarti più facilmente.')
                    ->columns(2)->collapsible()->collapsed()
                    ->schema([
                        UserFields::telegram(),
                        UserFields::instagram(),
                    ]),
                Section::make('Email')->schema([
                    CheckboxList::make('email_services')
                        ->label('Per quali servizi vuoi ricevere email?')
                        ->helperText('Le notifiche arrivano comunque in piattaforma. Puoi cambiare idea quando vuoi dal tuo profilo. Le email sul tuo account (sicurezza, accettazione, ...) arrivano sempre.')
                        ->options(config('vivere.services'))
                        ->default(array_keys(config('vivere.services')))
                        ->columns(2)
                        ->bulkToggleable(),
                ]),
                Checkbox::make('privacy')
                    ->label(self::privacyLabel())
                    ->accepted()
                    ->validationMessages(['accepted' => 'Per registrarti devi accettare l\'informativa sulla privacy.'])
                    ->dehydrated(false),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(#[SensitiveParameter] array $data): Model
    {
        $services = $data['email_services'] ?? [];
        unset($data['email_services']);

        $user = User::create([
            ...$data,
            'privacy_accepted_at' => now(),
            'terms_version' => config('vivere.terms_version'),
        ]);
        $user->course_year_confirmed_at = now(); // L'anno è appena stato dichiarato
        $user->save();

        foreach (array_keys(config('vivere.services')) as $service) {
            EmailPreference::create([
                'user_id' => $user->id,
                'service' => $service,
                'enabled' => in_array($service, $services, true),
            ]);
        }

        // Ricarica i default del DB (es. status = Pending)
        return $user->refresh();
    }

    /**
     * Propone lo username (Nome.Cognome) appena sono noti nome e cognome, se l'utente
     * non l'ha già scritto lui.
     */
    private static function suggestUsername(Get $get, Set $set): void
    {
        if (blank($get('username')) && filled($get('name')) && filled($get('surname'))) {
            $set('username', UnipaIdentity::suggestedUsername((string) $get('name'), (string) $get('surname')));
        }
    }

    private static function privacyLabel(): HtmlString|string
    {
        $url = config('humanresources.privacy_policy_url');

        return $url
            ? new HtmlString('Ho letto e accetto l\'<a href="'.e($url).'" target="_blank" class="underline">informativa sulla privacy</a>')
            : 'Ho letto e accetto l\'informativa sulla privacy';
    }
}
