<?php

use App\Models\User;
use Filament\Auth\MultiFactor\Email\Notifications\VerifyEmailAuthentication;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Modules\HumanResources\Filament\Pages\Auth\Login;
use Modules\HumanResources\Filament\Pages\Auth\RequestPasswordReset;

/*
 * Login con username o email (HR 2.2.2) e reset della password con mail oscurata.
 */

beforeEach(function () {
    Filament::setCurrentPanel('hr');
    Notification::fake();
    $this->user = User::factory()->create([
        'username' => 'MarioLuigi.Rossi03',
        'email' => 'marioluigi.rossi03@community.unipa.it',
        'password' => 'Una-Password-Lunga-42',
    ]);
});

test('si accede con lo username, senza distinguere le maiuscole', function () {
    Livewire::test(Login::class)
        ->fillForm(['email' => 'marioluigi.ROSSI03', 'password' => 'Una-Password-Lunga-42'])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    // Credenziali giuste: parte il codice della 2FA via email
    Notification::assertSentTo($this->user, VerifyEmailAuthentication::class);
});

test('si accede anche con la mail', function () {
    Livewire::test(Login::class)
        ->fillForm(['email' => 'MarioLuigi.Rossi03@community.unipa.it', 'password' => 'Una-Password-Lunga-42'])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    Notification::assertSentTo($this->user, VerifyEmailAuthentication::class);
});

test('con la password sbagliata non si entra', function () {
    Livewire::test(Login::class)
        ->fillForm(['email' => 'MarioLuigi.Rossi03', 'password' => 'sbagliata'])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    Notification::assertNothingSent();
});

test('il reset della password si chiede con lo username e mostra la mail oscurata', function () {
    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => 'MarioLuigi.Rossi03'])
        ->call('request')
        ->assertNotified('Controlla la tua mail');

    Notification::assertSentTo($this->user, ResetPassword::class);
});
