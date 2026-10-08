<?php

namespace App\Models;

use App\Enums\CourseYear;
use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Concerns\Audited;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * Persona registrata alla suite (core.users). È l'unico model di utente: tutti i moduli lo usano.
 *
 * Contratti: FilamentUser (accesso ai panel), HasEmailAuthentication (2FA via email),
 * HasName (nome mostrato da Filament), MustVerifyEmail (senza questo Filament non applica
 * la verifica della mail, ->emailVerification() nel panel HR).
 *
 * @property string $id
 * @property string $name
 * @property string $surname
 * @property string $username
 * @property CarbonImmutable $birthday
 * @property CourseYear $course_year
 * @property CarbonImmutable|null $course_year_confirmed_at
 * @property string $phone_number
 * @property string $email
 * @property string|null $telegram_tag
 * @property string|null $instagram_tag
 * @property string $password
 * @property string|null $remember_token
 * @property Role $role
 * @property UserStatus $status
 * @property Gender|null $gender
 * @property CarbonImmutable|null $email_verified_at
 * @property CarbonImmutable $privacy_accepted_at
 * @property string $terms_version
 * @property CarbonImmutable|null $anonymized_at
 * @property string|null $course_id
 * @property string|null $school_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Table('core.users')]
#[Fillable([
    'name', 'surname', 'username', 'birthday', 'course_year', 'phone_number', 'email',
    'telegram_tag', 'instagram_tag', 'password', 'gender', 'privacy_accepted_at', 'terms_version',
    'course_id', 'school_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasEmailAuthentication, HasName, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use Audited, HasFactory, HasUuids, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * Nota: "role" e "status" non sono fillable di proposito. Si cambiano solo con
     * assegnazioni esplicite ($user->role = ...) dentro azioni autorizzate, mai da un form generico.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'course_year' => CourseYear::class,
            'course_year_confirmed_at' => 'datetime',
            'role' => Role::class,
            'status' => UserStatus::class,
            'gender' => Gender::class,
            'email_verified_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
            'anonymized_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * La mail si salva sempre in minuscolo: "Mario.Rossi@..." e "mario.rossi@..." sono lo stesso account.
     *
     * @return Attribute<string, string>
     */
    protected function email(): Attribute
    {
        return Attribute::make(set: fn (string $value): string => Str::lower(trim($value)));
    }

    // ----- Relazioni -----

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Dati aggiuntivi richiesti solo a staff e admin.
     *
     * @return HasOne<StaffProfile, $this>
     */
    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    /**
     * Mandati istituzionali (attuali e passati).
     *
     * @return HasMany<UserAcademicRole, $this>
     */
    public function userAcademicRoles(): HasMany
    {
        return $this->hasMany(UserAcademicRole::class);
    }

    /**
     * Corsi di cui l'utente è amministratore (anche diversi dal proprio).
     *
     * @return BelongsToMany<Course, $this>
     */
    public function administeredCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'core.admin_courses')->withPivot('created_at');
    }

    /**
     * Storico dei ban ricevuti.
     *
     * @return HasMany<Ban, $this>
     */
    public function bans(): HasMany
    {
        return $this->hasMany(Ban::class);
    }

    /**
     * Storico delle gestioni di aulette.
     *
     * @return HasMany<AulettaManager, $this>
     */
    public function aulettaManagements(): HasMany
    {
        return $this->hasMany(AulettaManager::class);
    }

    /** @return HasMany<EmailPreference, $this> */
    public function emailPreferences(): HasMany
    {
        return $this->hasMany(EmailPreference::class);
    }

    /**
     * Changelog già visti.
     *
     * @return BelongsToMany<Changelog, $this>
     */
    public function seenChangelogs(): BelongsToMany
    {
        return $this->belongsToMany(Changelog::class, 'core.changelog_user')->withPivot('seen_at');
    }

    /**
     * Sovrascrive la relazione del trait Notifiable per usare core.notifications invece
     * della tabella "notifications" in public. Il canale database di Laravel e la campanella
     * di Filament passano da qui.
     *
     * @return MorphMany<Notification, $this>
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    // ----- Helper -----

    /**
     * Vero se l'utente ha almeno i poteri del ruolo indicato (i ruoli sono gerarchici).
     */
    public function hasRoleAtLeast(Role $role): bool
    {
        return $this->role->isAtLeast($role);
    }

    /**
     * Vero se l'utente si è disiscritto e i suoi dati sono stati anonimizzati.
     */
    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }

    /**
     * Vero se l'utente vuole ricevere le email del servizio indicato (alias del modulo).
     * Senza una preferenza salvata la risposta è sì. Le email legate alla sicurezza e allo
     * stato dell'account (ban, 2FA, reset password) partono comunque.
     */
    public function wantsEmailsFor(string $service): bool
    {
        $preference = $this->relationLoaded('emailPreferences')
            ? $this->emailPreferences->firstWhere('service', $service)
            : $this->emailPreferences()->where('service', $service)->first();

        return $preference === null || $preference->enabled;
    }

    /**
     * Sceglie la variante di un testo in base al genere dichiarato, per declinare i messaggi
     * della piattaforma. Senza genere, o con "Altro", si usa la forma neutra.
     *
     * Esempio: $user->inflect('Benvenuto', 'Benvenuta', 'Ti diamo il benvenuto')
     */
    public function inflect(string $male, string $female, string $neutral): string
    {
        return match ($this->gender) {
            Gender::Male => $male,
            Gender::Female => $female,
            default => $neutral,
        };
    }

    /**
     * Restituisce le iniziali dell'utente
     */
    public function initials(): string
    {
        return Str::upper(Str::substr($this->name, 0, 1).Str::substr($this->surname, 0, 1));
    }

    // ----- Filament -----

    /**
     * Chi può entrare in ciascun panel. Ogni modulo con un panel deve avere qui la sua regola:
     * per i panel non elencati l'accesso è negato.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Un account anonimizzato non esiste più per nessuna piattaforma
        if ($this->isAnonymized()) {
            return false;
        }

        return match ($panel->getId()) {
            // HR è la porta d'ingresso della suite: ci entrano anche gli account in attesa
            // (vedranno la schermata di attesa), ma non i bannati
            'hr' => $this->status !== UserStatus::Banned,
            // Kaffettino: il conto si può aprire solo allo staff (documento, D8)
            'kaffettino' => $this->status === UserStatus::Active && $this->hasRoleAtLeast(Role::Staff),
            default => false,
        };
    }

    /**
     * Nome mostrato da Filament (menu utente, notifiche, ...).
     */
    public function getFilamentName(): string
    {
        return "{$this->name} {$this->surname}";
    }

    /**
     * La 2FA via email è obbligatoria per tutti (documento, HR 2.2.1): è sempre attiva
     * e non si può disattivare, quindi non serve una colonna nel DB.
     */
    public function hasEmailAuthentication(): bool
    {
        return true;
    }

    /**
     * Non fa nulla di proposito: la 2FA via email non è disattivabile (vedi hasEmailAuthentication()).
     */
    public function toggleEmailAuthentication(bool $condition): void {}
}
