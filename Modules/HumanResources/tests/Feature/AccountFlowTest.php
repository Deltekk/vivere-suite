<?php

use App\Enums\CourseYear;
use App\Models\Course;
use App\Models\School;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Modules\HumanResources\Filament\Pages\CompleteStaffProfile;
use Modules\HumanResources\Filament\Pages\ConfirmCourseYear;

/*
 * Percorso dell'account (EnsureAccountIsReady): attesa, profilo staff, mail UNIPA, conferma di ottobre.
 */

test('chi è in attesa vede solo la schermata di attesa', function () {
    $this->actingAs(User::factory()->pending()->create())->get('/hr')->assertRedirect('/hr/in-attesa');
    $this->actingAs(User::factory()->pending()->create())->get('/hr/in-attesa')->assertOk()->assertSee('in attesa');
});

test('lo staff senza profilo deve completarlo', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)->get('/hr')->assertRedirect('/hr/profilo-staff');
    $this->actingAs($staff)->get('/kaffettino')->assertRedirect('/hr/profilo-staff');
});

test('il profilo staff si completa con codice fiscale, luogo di nascita e scuola', function () {
    $staff = User::factory()->staff()->create(['school_id' => null]);
    $school = School::factory()->create();
    Filament::setCurrentPanel('hr');

    Livewire::actingAs($staff)->test(CompleteStaffProfile::class)
        ->fillForm([
            'tax_code' => 'rssmra00a01g273x',
            'birth_city' => 'Palermo',
            'birth_province' => 'PA',
            'birth_country' => 'Italia',
            'school_id' => $school->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($staff->fresh()->staffProfile->tax_code)->toBe('RSSMRA00A01G273X')
        ->and($staff->fresh()->school_id)->toBe($school->id);

    $this->actingAs($staff->fresh())->get('/hr')->assertOk();
});

test('chi non è più alle superiori deve passare alla mail UNIPA', function () {
    $user = User::factory()->create(['email' => 'mario@gmail.com', 'course_year' => CourseYear::T1]);

    $this->actingAs($user)->get('/hr')->assertRedirect('/hr/profile');
    $this->actingAs($user)->get('/hr/profile')->assertOk();
});

test('gli studenti delle superiori possono tenere la mail personale', function () {
    $this->actingAs(User::factory()->highSchool()->create(['status' => 'Active']))->get('/hr')->assertOk();
});

test('a ottobre chi non ha confermato l\'anno deve farlo', function () {
    $this->travelTo(CarbonImmutable::create(2026, 10, 15, 12, 0, 0, 'Europe/Rome'));
    $user = User::factory()->create(['course_year_confirmed_at' => CarbonImmutable::create(2026, 9, 1)]);

    $this->actingAs($user)->get('/hr')->assertRedirect('/hr/conferma-anno');

    Filament::setCurrentPanel('hr');
    $course = Course::factory()->create();
    Livewire::actingAs($user)->test(ConfirmCourseYear::class)
        ->fillForm(['course_year' => CourseYear::M1->value, 'course_id' => $course->id])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->course_year)->toBe(CourseYear::M1);
    $this->actingAs($user->fresh())->get('/hr')->assertOk();
});

test('fuori da ottobre la conferma dell\'anno non viene chiesta', function () {
    $this->travelTo(CarbonImmutable::create(2027, 3, 15, 12, 0, 0, 'Europe/Rome'));
    $user = User::factory()->create(['course_year_confirmed_at' => CarbonImmutable::create(2025, 1, 1)]);

    $this->actingAs($user)->get('/hr')->assertOk();
});
