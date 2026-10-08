<?php

use App\Models\User;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Auth\MultiFactor\Email\Notifications\VerifyEmailAuthentication;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

/*
 * 2FA via email (Filament). Il codice e la sua scadenza stanno in cache: la cache deve
 * serializzare come Redis, altrimenti il test non vede problemi di deserializzazione
 * (config/cache.php, "serializable_classes").
 */

beforeEach(function () {
    config(['cache.default' => 'array', 'cache.stores.array.serialize' => true]);
    Cache::forgetDriver('array');
});

/**
 * Invia un codice come fa la schermata di login e restituisce quello arrivato per mail.
 */
function sendEmailCode(EmailAuthentication $provider, User $user): string
{
    Notification::fake();

    expect($provider->sendCode($user))->toBeTrue();

    $code = null;
    Notification::assertSentTo($user, VerifyEmailAuthentication::class, function (VerifyEmailAuthentication $notification) use (&$code): bool {
        $code = $notification->code;

        return true;
    });

    return $code;
}

test('il codice ricevuto per mail viene accettato una sola volta', function () {
    $user = User::factory()->superAdmin()->create();
    $provider = EmailAuthentication::make();

    $code = sendEmailCode($provider, $user);

    expect($provider->verifyCode($code, $user))->toBeTrue()
        ->and($provider->verifyCode($code, $user))->toBeFalse();
});

test('un codice sbagliato viene rifiutato', function () {
    $user = User::factory()->create();
    $provider = EmailAuthentication::make();

    $code = sendEmailCode($provider, $user);

    expect($provider->verifyCode($code === '000000' ? '111111' : '000000', $user))->toBeFalse();
});

test('un codice scaduto viene rifiutato', function () {
    $user = User::factory()->create();
    $provider = EmailAuthentication::make();

    $code = sendEmailCode($provider, $user);
    $this->travel($provider->getCodeExpiryMinutes() + 1)->minutes();

    expect($provider->verifyCode($code, $user))->toBeFalse();
});

test('la cache non deserializza classi diverse dalle date', function () {
    Cache::put('oggetto', new ArrayObject([1, 2]));

    expect(Cache::get('oggetto'))->toBeInstanceOf(__PHP_Incomplete_Class::class);
});
