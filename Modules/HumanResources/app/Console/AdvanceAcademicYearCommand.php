<?php

namespace Modules\HumanResources\Console;

use Illuminate\Console\Command;
use Modules\HumanResources\Actions\AdvanceAcademicYear;

/**
 * Passaggio al nuovo anno accademico. Schedulato il giorno di inizio anno (config
 * humanresources.academic_year_start); si può lanciare a mano, non fa nulla due volte.
 */
class AdvanceAcademicYearCommand extends Command
{
    protected $signature = 'hr:advance-academic-year';

    protected $description = 'Passa tutti gli utenti al nuovo anno accademico (superiori: da confermare)';

    public function handle(AdvanceAcademicYear $advance): int
    {
        $updated = $advance->handle();

        $this->components->info($updated > 0
            ? "Utenti aggiornati: {$updated}"
            : 'Niente da fare: il passaggio di quest\'anno accademico è già stato eseguito.');

        return self::SUCCESS;
    }
}
