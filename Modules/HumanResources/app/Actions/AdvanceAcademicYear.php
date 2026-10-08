<?php

namespace Modules\HumanResources\Actions;

use App\Enums\UserStatus;
use App\Models\Activity;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Modules\HumanResources\Support\AcademicYear;

/**
 * Passaggio all'anno accademico successivo (HR 2.2.1), eseguito il giorno di inizio anno.
 *
 * - Universitari: avanzano di un anno secondo CourseYear::next(). Per tutto ottobre verrà loro
 *   chiesto di confermarlo (EnsureAccountIsReady).
 * - Superiori: restano invariati e passano a "da confermare": li conferma a mano un admin.
 * - Esclusi: account in attesa, bannati e anonimizzati.
 *
 * È idempotente: se per l'anno accademico corrente è già stato eseguito non fa nulla
 * (lo si ricava dall'audit log, che è append-only).
 */
class AdvanceAcademicYear
{
    /**
     * @return int Numero di utenti aggiornati (0 se già eseguito per quest'anno)
     */
    public function handle(?CarbonImmutable $at = null): int
    {
        $academicYearStart = AcademicYear::currentStart($at)->toDateString();

        $alreadyDone = Activity::query()
            ->where('event', 'academic_year_advanced')
            ->where('properties->academic_year_start', $academicYearStart)
            ->exists();

        if ($alreadyDone) {
            return 0;
        }

        $updated = 0;

        // Una sola voce di audit riassuntiva invece di una per utente
        activity()->withoutLogging(function () use (&$updated): void {
            User::query()
                ->whereIn('status', [UserStatus::Active, UserStatus::ToConfirm])
                ->whereNull('anonymized_at')
                ->chunkById(500, function (Collection $users) use (&$updated): void {
                    foreach ($users as $user) {
                        if ($user->course_year->isHighSchool()) {
                            $user->status = UserStatus::ToConfirm;
                        } else {
                            $user->course_year = $user->course_year->next();
                        }

                        if ($user->isDirty()) {
                            $user->save();
                            $updated++;
                        }
                    }
                });
        });

        activity('humanresources')->event('academic_year_advanced')
            ->withProperties(['academic_year_start' => $academicYearStart, 'updated_users' => $updated])
            ->log('Passaggio al nuovo anno accademico');

        return $updated;
    }
}
