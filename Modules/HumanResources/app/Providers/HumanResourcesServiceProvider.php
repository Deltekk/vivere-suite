<?php

namespace Modules\HumanResources\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\HumanResources\Console\AdvanceAcademicYearCommand;
use Modules\HumanResources\Console\ImportProfessorsCommand;
use Modules\HumanResources\Console\SyncProfessorsCommand;
use Modules\HumanResources\Contracts\ProfessorSource;
use Modules\HumanResources\Professors\NoProfessorSource;
use Modules\HumanResources\Providers\Filament\HumanResourcesPanelProvider;
use Nwidart\Modules\Support\ModuleServiceProvider;

class HumanResourcesServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'HumanResources';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'humanresources';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        AdvanceAcademicYearCommand::class,
        SyncProfessorsCommand::class,
        ImportProfessorsCommand::class,
    ];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
        // Interfaccia del modulo (panel Filament basato su VivereSuitePanelProvider)
        HumanResourcesPanelProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        // Fonte dei nomi dei professori. TODO: sostituire con uno scraper del sito di ateneo
        // (vedi NoProfessorSource); nel frattempo si usa "php artisan hr:import-professors".
        $this->app->bind(ProfessorSource::class, NoProfessorSource::class);
    }

    /**
     * Job schedulati del modulo (in sviluppo li esegue "schedule:work" dentro composer dev).
     */
    protected function configureSchedules(Schedule $schedule): void
    {
        // Passaggio al nuovo anno accademico, il giorno di inizio anno (default 1 ottobre) alle 3 di notte
        [$month, $day] = array_map('intval', explode('-', config('humanresources.academic_year_start')));
        $schedule->command('hr:advance-academic-year')->yearlyOn($month, max(1, min(31, $day)), '03:00')->timezone(config('vivere.display_timezone'));

        // Elenco dei professori per gli avvisi in registrazione (job isolato, D12)
        $schedule->command('hr:sync-professors')->weekly()->timezone(config('vivere.display_timezone'));
    }
}
