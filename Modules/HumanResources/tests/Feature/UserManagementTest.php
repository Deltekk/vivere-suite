<?php

use App\Enums\KeyState;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Activity;
use App\Models\Auletta;
use App\Models\Ban;
use App\Models\EmailPreference;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Notification;
use Modules\HumanResources\Actions\AcceptRegistration;
use Modules\HumanResources\Actions\AnonymizeUser;
use Modules\HumanResources\Actions\BanUser;
use Modules\HumanResources\Actions\ChangeUserRole;
use Modules\HumanResources\Actions\CreateUser;
use Modules\HumanResources\Actions\ManageAulettaManagers;
use Modules\HumanResources\Actions\SendBroadcast;
use Modules\HumanResources\Actions\UnbanUser;
use Modules\HumanResources\Notifications\AccountApproved;
use Modules\HumanResources\Notifications\AccountBanned;
use Modules\HumanResources\Notifications\AulettaKeysPickup;
use Modules\HumanResources\Notifications\AulettaKeysReturn;
use Modules\HumanResources\Notifications\BroadcastMessage;
use Modules\HumanResources\Notifications\WelcomeSetPassword;
use Modules\HumanResources\Support\RegistrationWarnings;

/*
 * Gestione degli utenti da parte di staff e admin (HR 2.2.3-2.2.4) e relativi permessi.
 */

beforeEach(function () {
    Notification::fake();
    $this->staff = User::factory()->staff()->create();
    $this->admin = User::factory()->admin()->create();
});

test('uno staffer accetta una registrazione e l\'utente viene avvisato', function () {
    $pending = User::factory()->pending()->create();

    app(AcceptRegistration::class)->handle($pending, $this->staff);

    expect($pending->fresh()->status)->toBe(UserStatus::Active);
    Notification::assertSentTo($pending, AccountApproved::class);
});

test('uno studente non può accettare registrazioni', function () {
    app(AcceptRegistration::class)->handle(User::factory()->pending()->create(), User::factory()->create());
})->throws(AuthorizationException::class);

test('un admin banna con motivazione: storico, mail e audit', function () {
    $user = User::factory()->create(['email' => 'mario.rossi07@community.unipa.it']);

    app(BanUser::class)->handle($user, 'Comportamento offensivo in auletta', $this->admin);

    expect($user->fresh()->status)->toBe(UserStatus::Banned)
        ->and(Ban::first()->email_pattern)->toBe('mario.rossi')
        ->and(Activity::where('event', 'banned')->first()->properties['reason'])->toBe('Comportamento offensivo in auletta');
    Notification::assertSentTo($user, AccountBanned::class, fn (AccountBanned $notification): bool => $notification->reason === 'Comportamento offensivo in auletta');
});

test('chi si registra con un\'altra mail della stessa persona bannata riceve un avviso, non un rifiuto', function () {
    app(BanUser::class)->handle(User::factory()->create(['email' => 'mario.rossi@community.unipa.it']), 'Spam ripetuto nei gruppi', $this->admin);
    $again = User::factory()->pending()->create(['name' => 'Mario', 'surname' => 'Rossi', 'email' => 'mario.rossi02@community.unipa.it']);

    expect(RegistrationWarnings::for($again))->toHaveCount(1)
        ->and($again->status)->toBe(UserStatus::Pending);
});

test('lo staff non può bannare e nessuno può bannare un super admin', function (string $who) {
    $target = $who === 'superadmin' ? User::factory()->superAdmin()->create() : User::factory()->create();
    $by = $who === 'superadmin' ? $this->admin : $this->staff;

    app(BanUser::class)->handle($target, 'Motivazione di prova', $by);
})->with(['staff', 'superadmin'])->throws(AuthorizationException::class);

test('un admin revoca il ban', function () {
    $user = User::factory()->create();
    app(BanUser::class)->handle($user, 'Motivazione di prova', $this->admin);

    app(UnbanUser::class)->handle($user->fresh(), $this->admin);

    expect($user->fresh()->status)->toBe(UserStatus::Active)->and(Ban::count())->toBe(1);
});

test('un admin cambia il ruolo, ma non il proprio né quello di un super admin, e non assegna super admin', function () {
    $user = User::factory()->create();
    app(ChangeUserRole::class)->handle($user, Role::Staff, $this->admin);
    expect($user->fresh()->role)->toBe(Role::Staff);

    expect(fn () => app(ChangeUserRole::class)->handle($this->admin, Role::Student, $this->admin))->toThrow(AuthorizationException::class)
        ->and(fn () => app(ChangeUserRole::class)->handle(User::factory()->superAdmin()->create(), Role::Admin, $this->admin))->toThrow(AuthorizationException::class)
        ->and(fn () => app(ChangeUserRole::class)->handle($user, Role::SuperAdmin, $this->admin))->toThrow(InvalidArgumentException::class);
});

test('un admin inserisce un utente, che riceve la mail per scegliere la password', function () {
    $user = app(CreateUser::class)->handle([
        'name' => 'Giulia',
        'surname' => 'Verdi',
        'username' => 'Giulia.Verdi',
        'birthday' => '2003-05-10',
        'course_year' => 'T2',
        'phone_number' => '+39 333 0000000',
        'email' => 'giulia.verdi@community.unipa.it',
    ], $this->admin);

    expect($user->status)->toBe(UserStatus::Active);
    Notification::assertSentTo($user, WelcomeSetPassword::class);
});

test('i gestori delle aulette ricevono le mail per le chiavi', function () {
    $auletta = Auletta::factory()->create();
    $managers = app(ManageAulettaManagers::class);

    $management = $managers->assign($this->staff, $auletta, $this->admin);
    Notification::assertSentTo($this->staff, AulettaKeysPickup::class);

    $managers->updateKeys($management, KeyState::HandedOver, $this->admin);
    $managers->remove($management->fresh(), $this->admin);

    expect($management->fresh()->keys_state)->toBe(KeyState::Requested)->and($management->fresh()->removed_at)->not->toBeNull();
    Notification::assertSentTo($this->staff, AulettaKeysReturn::class);
});

test('uno studente non può gestire un\'auletta', function () {
    app(ManageAulettaManagers::class)->assign(User::factory()->create(), Auletta::factory()->create(), $this->admin);
})->throws(InvalidArgumentException::class);

test('la notifica broadcast arriva per mail solo a chi vuole le email di HR', function () {
    $wantsMail = User::factory()->create();
    $noMail = User::factory()->create();
    EmailPreference::create(['user_id' => $noMail->id, 'service' => 'humanresources', 'enabled' => false]);

    $count = app(SendBroadcast::class)->handle('Assemblea', "Ci vediamo giovedì.\n\nPortate idee!", ['roles' => ['Student']], $this->admin);

    expect($count)->toBe(2);
    Notification::assertSentTo($wantsMail, BroadcastMessage::class, fn ($notification, array $channels): bool => in_array('mail', $channels, true));
    Notification::assertSentTo($noMail, BroadcastMessage::class, fn ($notification, array $channels): bool => $channels === ['database']);
});

test('chi si disiscrive viene anonimizzato e non può più entrare', function () {
    $user = User::factory()->create(['email' => 'mario.rossi@community.unipa.it']);

    app(AnonymizeUser::class)->handle($user);

    $user = $user->fresh();
    expect($user->isAnonymized())->toBeTrue()
        ->and($user->email)->not->toContain('mario')
        ->and($user->name)->toBe('Utente');

    $this->actingAs($user)->get('/hr')->assertForbidden();
});

test('lo staff vede gli utenti ma non i dati riservati, che vede l\'admin', function () {
    $user = User::factory()->staff()->withStaffProfile()->create();
    $staff = User::factory()->staff()->withStaffProfile()->create();
    $admin = User::factory()->admin()->withStaffProfile()->create();

    $this->actingAs($staff)->get("/hr/utenti/{$user->id}")->assertOk()->assertDontSee($user->staffProfile->tax_code);
    $this->actingAs($admin)->get("/hr/utenti/{$user->id}")->assertOk()->assertSee($user->staffProfile->tax_code);
});

test('l\'audit log è visibile solo ai super admin', function () {
    $this->actingAs(User::factory()->admin()->withStaffProfile()->create())->get('/hr/audit-log')->assertForbidden();
    $this->actingAs(User::factory()->superAdmin()->withStaffProfile()->create())->get('/hr/audit-log')->assertOk();
});
