<?php

use App\Enums\CourseYear;
use App\Enums\UserStatus;
use App\Models\Course;
use App\Models\EmailPreference;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Modules\HumanResources\Filament\Pages\Auth\Register;
use Modules\HumanResources\Notifications\RegistrationAwaitingReview;

/*
 * Registrazione (HR 2.2.1): dati richiesti, regole e avviso agli admin del corso.
 */

beforeEach(function () {
    Filament::setCurrentPanel('hr');
    Notification::fake();
    $this->course = Course::factory()->create();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function registrationData(array $overrides = []): array
{
    return [
        'name' => 'Mario Luigi',
        'surname' => 'Rossi',
        'birthday' => now()->subYears(20)->toDateString(),
        'phone_number' => '+39 333 1234567',
        'course_year' => CourseYear::T1->value,
        'course_id' => test()->course->id,
        'email' => 'marioluigi.rossi03@community.unipa.it',
        'username' => 'MarioLuigi.Rossi03',
        'password' => 'Una-Password-Lunga-42',
        'passwordConfirmation' => 'Una-Password-Lunga-42',
        'email_services' => ['humanresources'],
        'privacy' => true,
        ...$overrides,
    ];
}

test('la registrazione crea un account in attesa con le preferenze email', function () {
    Livewire::test(Register::class)->fillForm(registrationData())->call('register')->assertHasNoFormErrors();

    $user = User::firstWhere('username', 'MarioLuigi.Rossi03');

    expect($user->status)->toBe(UserStatus::Pending)
        ->and($user->terms_version)->toBe(config('vivere.terms_version'))
        ->and($user->privacy_accepted_at)->not->toBeNull()
        ->and($user->wantsEmailsFor('humanresources'))->toBeTrue()
        ->and($user->wantsEmailsFor('kaffettino'))->toBeFalse()
        ->and(EmailPreference::where('user_id', $user->id)->count())->toBe(count(config('vivere.services')));
});

test('gli admin del corso vengono avvisati della nuova registrazione', function () {
    $courseAdmin = User::factory()->admin()->create();
    $this->course->administrators()->attach($courseAdmin);
    $otherAdmin = User::factory()->admin()->create();

    Livewire::test(Register::class)->fillForm(registrationData())->call('register');

    Notification::assertSentTo($courseAdmin, RegistrationAwaitingReview::class);
    Notification::assertNotSentTo($otherAdmin, RegistrationAwaitingReview::class);
});

test('se il corso non ha admin vengono avvisati tutti gli admin', function () {
    $admin = User::factory()->admin()->create();

    Livewire::test(Register::class)->fillForm(registrationData())->call('register');

    Notification::assertSentTo($admin, RegistrationAwaitingReview::class);
});

test('chi ha meno di 16 anni non può registrarsi', function () {
    Livewire::test(Register::class)
        ->fillForm(registrationData(['birthday' => now()->subYears(15)->toDateString()]))
        ->call('register')
        ->assertHasFormErrors(['birthday']);
});

test('gli universitari devono usare la mail UNIPA', function () {
    Livewire::test(Register::class)
        ->fillForm(registrationData(['email' => 'mario@gmail.com']))
        ->call('register')
        ->assertHasFormErrors(['email']);
});

test('gli studenti delle superiori possono usare una mail personale e non hanno corso', function () {
    Livewire::test(Register::class)
        ->fillForm(registrationData([
            'email' => 'mario.rossi@gmail.com',
            'course_year' => CourseYear::S5->value,
            'birthday' => now()->subYears(17)->toDateString(),
        ]))
        ->call('register')
        ->assertHasNoFormErrors();

    expect(User::firstWhere('email', 'mario.rossi@gmail.com')->course_id)->toBeNull();
});

test('lo username deve rispettare il formato Nome.Cognome ed essere unico', function () {
    Livewire::test(Register::class)->fillForm(registrationData(['username' => 'mario rossi']))->call('register')->assertHasFormErrors(['username']);

    User::factory()->create(['username' => 'Mario.Bianchi']);
    Livewire::test(Register::class)->fillForm(registrationData(['username' => 'mario.bianchi']))->call('register')->assertHasFormErrors(['username']);
});

test('l\'informativa privacy va accettata', function () {
    Livewire::test(Register::class)->fillForm(registrationData(['privacy' => false]))->call('register')->assertHasFormErrors(['privacy']);
});

test('la password deve essere forte', function () {
    Livewire::test(Register::class)
        ->fillForm(registrationData(['password' => 'corta', 'passwordConfirmation' => 'corta']))
        ->call('register')
        ->assertHasFormErrors(['password']);
});
