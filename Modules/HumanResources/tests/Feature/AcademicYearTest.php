<?php

use App\Enums\CourseYear;
use App\Enums\UserStatus;
use App\Models\User;
use Carbon\CarbonImmutable;
use Modules\HumanResources\Actions\AdvanceAcademicYear;
use Modules\HumanResources\Support\AcademicYear;

/*
 * Passaggio d'anno accademico (HR 2.2.1).
 */

test('gli universitari avanzano di un anno e le superiori diventano da confermare', function () {
    $first = User::factory()->create(['course_year' => CourseYear::T1]);
    $last = User::factory()->create(['course_year' => CourseYear::T3]);
    $partTime = User::factory()->create(['course_year' => CourseYear::PTT_1_2]);
    $highSchool = User::factory()->highSchool()->create(['status' => UserStatus::Active, 'course_year' => CourseYear::S4]);
    $pending = User::factory()->pending()->create(['course_year' => CourseYear::T1]);

    app(AdvanceAcademicYear::class)->handle();

    expect($first->fresh()->course_year)->toBe(CourseYear::T2)
        ->and($last->fresh()->course_year)->toBe(CourseYear::TFC)
        ->and($partTime->fresh()->course_year)->toBe(CourseYear::PTT_2_1)
        ->and($highSchool->fresh()->course_year)->toBe(CourseYear::S4)
        ->and($highSchool->fresh()->status)->toBe(UserStatus::ToConfirm)
        ->and($pending->fresh()->course_year)->toBe(CourseYear::T1);
});

test('il passaggio non viene ripetuto due volte nello stesso anno accademico', function () {
    $user = User::factory()->create(['course_year' => CourseYear::T1]);

    app(AdvanceAcademicYear::class)->handle();
    expect(app(AdvanceAcademicYear::class)->handle())->toBe(0)
        ->and($user->fresh()->course_year)->toBe(CourseYear::T2);
});

test('l\'anno accademico inizia il primo ottobre', function () {
    expect(AcademicYear::currentStart(CarbonImmutable::create(2026, 9, 30, 12))->toDateString())->toBe('2025-10-01')
        ->and(AcademicYear::currentStart(CarbonImmutable::create(2026, 10, 1, 12))->toDateString())->toBe('2026-10-01')
        ->and(AcademicYear::isConfirmationPeriod(CarbonImmutable::create(2026, 10, 20, 12)))->toBeTrue()
        ->and(AcademicYear::isConfirmationPeriod(CarbonImmutable::create(2026, 11, 2, 12)))->toBeFalse();
});
