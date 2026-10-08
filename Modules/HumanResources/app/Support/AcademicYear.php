<?php

namespace Modules\HumanResources\Support;

use Carbon\CarbonImmutable;

/**
 * Date dell'anno accademico, calcolate nel fuso italiano.
 * L'inizio è configurato in config('humanresources.academic_year_start') (default 1 ottobre).
 */
final class AcademicYear
{
    /**
     * Inizio dell'anno accademico in corso alla data indicata (di default oggi).
     */
    public static function currentStart(?CarbonImmutable $at = null): CarbonImmutable
    {
        $at = ($at ?? CarbonImmutable::now())->setTimezone(config('vivere.display_timezone'));
        [$month, $day] = array_map('intval', explode('-', config('humanresources.academic_year_start')));

        $start = $at->setDate($at->year, $month, $day)->startOfDay();

        return $start->greaterThan($at) ? $start->subYear() : $start;
    }

    /**
     * Vero nel mese di inizio dell'anno accademico (ottobre): è il periodo in cui
     * agli utenti viene chiesto di confermare l'anno frequentato (HR 2.2.1).
     */
    public static function isConfirmationPeriod(?CarbonImmutable $at = null): bool
    {
        $at = ($at ?? CarbonImmutable::now())->setTimezone(config('vivere.display_timezone'));

        return $at->month === self::currentStart($at)->month;
    }
}
