<?php

use App\Models\AcademicRole;
use App\Models\Course;
use App\Models\Notification;
use App\Models\StaffProfile;
use App\Models\User;
use App\Models\UserAcademicRole;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/*
 * Verifica che le entità condivise vivano nello schema "core" e che relazioni, vincoli
 * e convenzioni (UUID, minuscole, pivot) funzionino come descritto in CLAUDE.md.
 */

test('gli utenti stanno nello schema core con id UUID', function () {
    $user = User::factory()->create();

    expect($user->id)->toBeUuid()
        ->and(DB::table('core.users')->where('id', $user->id)->exists())->toBeTrue();
});

test('le relazioni attraversano lo schema core', function () {
    $user = User::factory()->create();

    expect($user->course->department->macroarea)->not->toBeNull();
});

test('la mail viene salvata in minuscolo', function () {
    $user = User::factory()->create(['email' => 'Mario.Rossi@Community.UNIPA.it']);

    expect($user->fresh()->email)->toBe('mario.rossi@community.unipa.it');
});

test('lo username è unico senza distinguere maiuscole e minuscole', function () {
    User::factory()->create(['username' => 'Mario.Rossi']);

    User::factory()->create(['username' => 'mario.rossi']);
})->throws(QueryException::class);

test('il profilo staff è 1:1 con l\'utente', function () {
    $profile = StaffProfile::factory()->create();

    expect($profile->user->staffProfile->is($profile))->toBeTrue();

    StaffProfile::factory()->for($profile->user)->create();
})->throws(QueryException::class);

test('i mandati istituzionali conservano lo storico', function () {
    $user = User::factory()->create();
    $role = AcademicRole::factory()->create();

    UserAcademicRole::factory()->for($user)->for($role)->expired()->create();
    UserAcademicRole::factory()->for($user)->for($role)->create();

    expect($user->userAcademicRoles)->toHaveCount(2)
        ->and($user->userAcademicRoles()->active()->count())->toBe(1);
});

test('un mandato non può scadere prima di iniziare', function () {
    UserAcademicRole::factory()->create(['started_at' => now(), 'expires_at' => now()->subDay()]);
})->throws(QueryException::class);

test('gli admin di corso usano la pivot core.admin_courses', function () {
    $admin = User::factory()->admin()->create();
    $course = Course::factory()->create();

    $admin->administeredCourses()->attach($course);

    expect($course->administrators->first()->is($admin))->toBeTrue();
});

test('le notifiche database finiscono in core.notifications', function () {
    $user = User::factory()->create();

    Filament\Notifications\Notification::make()->title('Benvenuto')->sendToDatabase($user);

    expect($user->notifications)->toHaveCount(1)
        ->and($user->notifications->first())->toBeInstanceOf(Notification::class)
        ->and(DB::table('core.notifications')->value('notifiable_type'))->toBe('user');
});

test('le regole unique vanno scritte con la classe del model, non con "core.tabella"', function () {
    User::factory()->create(['email' => 'preso@community.unipa.it']);

    $validator = Validator::make(
        ['email' => 'preso@community.unipa.it'],
        ['email' => [Rule::unique(User::class, 'email')]],
    );

    expect($validator->fails())->toBeTrue();
});
