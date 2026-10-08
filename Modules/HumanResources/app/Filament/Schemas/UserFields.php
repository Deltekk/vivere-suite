<?php

namespace Modules\HumanResources\Filament\Schemas;

use App\Enums\CourseYear;
use App\Enums\Gender;
use App\Models\User;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\HumanResources\Support\UnipaIdentity;

/**
 * Campi dei dati di un utente, condivisi da registrazione, gestione utenti e profilo:
 * le regole del documento (HR 2.2.1) sono scritte una volta sola, qui.
 */
final class UserFields
{
    public static function name(): TextInput
    {
        return TextInput::make('name')->label('Nome')->required()->maxLength(255)
            ->helperText('Tutti i nomi, come nella mail istituzionale (es. Mario Luigi).');
    }

    public static function surname(): TextInput
    {
        return TextInput::make('surname')->label('Cognome')->required()->maxLength(255);
    }

    /**
     * Data di nascita completa: serve a verificare i 16 anni (D4) e per il caffè del compleanno.
     */
    public static function birthday(): DatePicker
    {
        $minimumAge = (int) config('humanresources.minimum_age');

        return DatePicker::make('birthday')->label('Data di nascita')->required()
            ->native(false)->displayFormat('d/m/Y')
            ->maxDate(now(config('vivere.display_timezone'))->subYears($minimumAge))
            ->validationMessages(['before_or_equal' => "Per registrarti devi avere almeno {$minimumAge} anni."]);
    }

    public static function gender(): Select
    {
        return Select::make('gender')->label('Genere (facoltativo)')
            ->options(Gender::class)
            ->helperText('Serve solo a usare la forma giusta nei testi (es. benvenuto/benvenuta).');
    }

    /**
     * Numero di telefono: obbligatorio, serve allo staff per contattarti (es. oggetti smarriti, D5).
     */
    public static function phone(): TextInput
    {
        return TextInput::make('phone_number')->label('Numero di telefono')->tel()->required()
            ->regex('/^\+?[0-9 ]{6,20}$/')
            ->helperText('Lo staff lo usa solo per contattarti, per esempio se hai perso un oggetto.');
    }

    /**
     * Email: istituzionale UNIPA, tranne per gli studenti delle superiori.
     */
    public static function email(): TextInput
    {
        return TextInput::make('email')->label('Email')->email()->required()->maxLength(255)
            ->unique(User::class, 'email', ignoreRecord: true)
            ->dehydrateStateUsing(fn (?string $state): ?string => $state === null ? null : Str::lower(trim($state)))
            ->rule(fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                $courseYear = self::courseYearFromState($get('course_year'));

                if ($courseYear?->isHighSchool() !== true && ! UnipaIdentity::isInstitutionalEmail((string) $value)) {
                    $fail('Serve la mail istituzionale UNIPA (es. nome.cognome@community.unipa.it). Solo chi frequenta le superiori può usarne un\'altra.');
                }
            })
            ->helperText('Mail istituzionale UNIPA. Se frequenti le superiori puoi usare la tua mail personale.');
    }

    /**
     * Username nel formato Nome.Cognome (D1), unico senza distinguere maiuscole.
     */
    public static function username(): TextInput
    {
        return TextInput::make('username')->label('Username')->required()->maxLength(100)
            ->regex(UnipaIdentity::USERNAME_REGEX)
            ->validationMessages(['regex' => 'Usa il formato Nome.Cognome, eventualmente con dei numeri finali (es. MarioLuigi.Rossi03).'])
            ->rule(self::uniqueIgnoringCase('username'))
            ->helperText('Formato Nome.Cognome, come nella mail UNIPA (es. MarioLuigi.Rossi03).');
    }

    public static function courseYear(): Select
    {
        return Select::make('course_year')->label('Anno di corso')
            ->options(CourseYear::class)
            ->required()
            ->live();
    }

    /**
     * Corso: obbligatorio per gli universitari, assente per le superiori.
     */
    public static function course(): Select
    {
        return Select::make('course_id')->label('Corso di studi')
            ->relationship('course', 'name')
            ->searchable()->preload()
            ->hidden(fn (Get $get): bool => self::courseYearFromState($get('course_year'))?->isHighSchool() === true)
            ->required(fn (Get $get): bool => self::courseYearFromState($get('course_year'))?->isHighSchool() !== true)
            ->dehydrated(fn (Get $get): bool => self::courseYearFromState($get('course_year'))?->isHighSchool() !== true);
    }

    /**
     * Scuola di provenienza: facoltativa, ma obbligatoria per lo staff (Orientamento).
     * Se la scuola non è in elenco la si può aggiungere al volo.
     */
    public static function school(bool $required = false): Select
    {
        return Select::make('school_id')->label('Scuola di provenienza'.($required ? '' : ' (facoltativa)'))
            ->relationship('school', 'name')
            ->getOptionLabelFromRecordUsing(fn (Model $record): string => "{$record->getAttribute('name')} ({$record->getAttribute('city')})")
            ->searchable(['name', 'city'])
            ->required($required)
            ->createOptionForm([
                TextInput::make('name')->label('Nome della scuola')->required()->maxLength(255),
                TextInput::make('city')->label('Città')->required()->maxLength(255),
            ]);
    }

    public static function telegram(): TextInput
    {
        return self::socialTag('telegram_tag', 'Tag Telegram (facoltativo)');
    }

    public static function instagram(): TextInput
    {
        return self::socialTag('instagram_tag', 'Tag Instagram (facoltativo)');
    }

    /**
     * @param  'telegram_tag'|'instagram_tag'  $column
     */
    private static function socialTag(string $column, string $label): TextInput
    {
        return TextInput::make($column)->label($label)->maxLength(64)->prefix('@')
            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? ltrim(trim($state), '@') : null)
            ->regex('/^@?[A-Za-z0-9_.]{3,32}$/')
            ->rule(self::uniqueIgnoringCase($column));
    }

    /**
     * Anno di corso dallo stato del form: in creazione è una stringa, in modifica l'enum.
     */
    public static function courseYearFromState(mixed $state): ?CourseYear
    {
        return $state instanceof CourseYear ? $state : CourseYear::tryFrom((string) $state);
    }

    /**
     * Unicità senza distinguere maiuscole/minuscole (come gli indici lower(...) del DB).
     * La regola unique di Laravel distingue le maiuscole, per questo serve una regola a parte.
     */
    /**
     * @param  'username'|'telegram_tag'|'instagram_tag'  $column
     */
    private static function uniqueIgnoringCase(string $column): Closure
    {
        return fn (?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($column, $record): void {
            $value = ltrim(trim((string) $value), '@');

            if ($value === '') {
                return;
            }

            // Query letterali (niente nomi di colonna interpolati nell'SQL)
            $condition = match ($column) {
                'username' => 'lower(username) = ?',
                'telegram_tag' => 'lower(telegram_tag) = ?',
                'instagram_tag' => 'lower(instagram_tag) = ?',
            };

            $taken = User::query()
                ->whereRaw($condition, [Str::lower($value)])
                ->when($record !== null, fn ($query) => $query->whereKeyNot($record->getKey()))
                ->exists();

            if ($taken) {
                $fail('Questo valore è già usato da un altro account.');
            }
        };
    }
}
