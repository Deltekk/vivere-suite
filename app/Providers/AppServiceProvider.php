<?php

namespace App\Providers;

use App\Models\AcademicRole;
use App\Models\Auletta;
use App\Models\AulettaManager;
use App\Models\Ban;
use App\Models\Building;
use App\Models\Changelog;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Department;
use App\Models\Device;
use App\Models\EmailPreference;
use App\Models\Macroarea;
use App\Models\Notification;
use App\Models\School;
use App\Models\StaffProfile;
use App\Models\User;
use App\Models\UserAcademicRole;
use Carbon\CarbonImmutable;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\DevCommands;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureDevCommands();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        // Policy password del documento (HR 2.2.1): almeno 12 caratteri, maiuscole, minuscole, numeri,
        // simboli, e non presente in data breach noti (uncompromised, servizio Have I Been Pwned).
        // Vale in ogni ambiente, così i bug si vedono già in sviluppo; nei test si salta solo
        // il controllo online, per non dipendere dalla rete.
        Password::defaults(function (): Password {
            $rule = Password::min(12)->mixedCase()->letters()->numbers()->symbols();

            return app()->runningUnitTests() ? $rule : $rule->uncompromised();
        });

        // Nelle colonne polimorfiche (es. core.notifications.notifiable_type, core.activity_log.subject_type)
        // salviamo un alias invece del nome della classe PHP: rinominare o spostare un model non rompe i dati.
        // Ogni model usato in una relazione polimorfica (quindi anche ogni model con il trait Audited)
        // va aggiunto qui; i moduli aggiungono i loro nel proprio ServiceProvider con Relation::morphMap().
        Relation::enforceMorphMap([
            'user' => User::class,
            'staff_profile' => StaffProfile::class,
            'school' => School::class,
            'academic_role' => AcademicRole::class,
            'user_academic_role' => UserAcademicRole::class,
            'macroarea' => Macroarea::class,
            'department' => Department::class,
            'building' => Building::class,
            'auletta' => Auletta::class,
            'course' => Course::class,
            'classroom' => Classroom::class,
            'ban' => Ban::class,
            'auletta_manager' => AulettaManager::class,
            'email_preference' => EmailPreference::class,
            'changelog' => Changelog::class,
            'device' => Device::class,
            'notification' => Notification::class,
        ]);

        // Il DB e l'app lavorano in UTC; Filament mostra le date nel fuso italiano
        FilamentTimezone::set(config('vivere.display_timezone'));
    }

    /**
     * Processi aggiuntivi lanciati da "php artisan dev" (vedi composer dev in coseUtili.md).
     * Laravel ha già server, queue, logs (pail) e vite: qui si aggiungono quelli della suite.
     */
    protected function configureDevCommands(): void
    {
        if (! $this->app->isLocal()) {
            return;
        }

        // Esegue i job schedulati (scraping, mail settimanali, ...) ogni minuto, come il cron in produzione
        DevCommands::artisan('schedule:work', 'scheduler');

        // Mostra le mail catturate da Mailpit appena arrivano, con il testo (codici 2FA, notifiche, ...)
        DevCommands::artisan('vivere:mail --watch --ansi', 'mail');
    }
}
