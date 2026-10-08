<?php

namespace Modules\HumanResources\Actions;

use App\Enums\CourseYear;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Un admin conferma l'anno di corso di un utente "da confermare" dopo il passaggio d'anno
 * (es. studenti delle superiori, HR 2.2.1) e lo riattiva.
 */
class ConfirmCourseYear
{
    public function handle(User $user, CourseYear $courseYear, ?string $courseId, User $confirmedBy): void
    {
        Gate::forUser($confirmedBy)->authorize('confirmYear', $user);

        $user->course_year = $courseYear;
        $user->course_id = $courseYear->isHighSchool() ? null : $courseId;
        $user->course_year_confirmed_at = now();
        $user->status = UserStatus::Active;
        $user->save();
    }
}
