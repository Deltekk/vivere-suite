<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/*
 * php artisan vivere:mail: legge le mail catturate da Mailpit.
 * L'API di Mailpit è simulata con Http::fake, così i test non richiedono il container.
 */

function fakeMailpit(array $messages, array $details = []): void
{
    Http::fake([
        '*/api/v1/messages*' => Http::response(['messages' => $messages]),
        '*/api/v1/message/*' => fn ($request) => Http::response($details[basename($request->url())] ?? [], isset($details[basename($request->url())]) ? 200 : 404),
    ]);
}

test('--latest mostra destinatario, oggetto e testo dell\'ultima mail', function () {
    fakeMailpit(
        [['ID' => 'abc', 'Created' => now()->toIso8601String(), 'To' => [['Address' => 'mario.rossi@community.unipa.it']], 'Subject' => 'Il tuo codice di accesso']],
        ['abc' => [
            'ID' => 'abc',
            'Date' => now()->toIso8601String(),
            'From' => ['Address' => 'noreply@example.com'],
            'To' => [['Address' => 'mario.rossi@community.unipa.it']],
            'Subject' => 'Il tuo codice di accesso',
            'Text' => "Il tuo codice di accesso è: 123456\n\n\n\nQuesto codice scadrà tra 4 minuti.",
        ]],
    );

    $this->artisan('vivere:mail --latest')
        ->expectsOutputToContain('mario.rossi@community.unipa.it')
        ->expectsOutputToContain('Il tuo codice di accesso è: 123456')
        ->expectsOutputToContain('/view/abc')
        ->assertSuccessful();
});

test('se la mail ha solo HTML mostra il testo senza tag', function () {
    fakeMailpit(
        [['ID' => 'html', 'Created' => now()->toIso8601String(), 'To' => [], 'Subject' => 'Solo HTML']],
        ['html' => ['ID' => 'html', 'From' => [], 'To' => [], 'Subject' => 'Solo HTML', 'Text' => '', 'HTML' => '<p>Ciao <b>Mario</b></p>']],
    );

    $this->artisan('vivere:mail --latest')
        ->expectsOutputToContain('Ciao Mario')
        ->assertSuccessful();
});

test('senza mail lo dice ed esce senza errori', function () {
    fakeMailpit([]);

    $this->artisan('vivere:mail --latest')
        ->expectsOutputToContain('Nessuna mail')
        ->assertSuccessful();
});

test('se Mailpit non risponde spiega come avviarlo', function () {
    Http::fake(fn () => throw new ConnectionException('Connection refused'));

    $this->artisan('vivere:mail --latest')
        ->expectsOutputToContain('composer services')
        ->assertFailed();
});
