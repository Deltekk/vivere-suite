<?php

use App\Enums\UserStatus;
use App\Models\User;

/*
 * Accesso ai panel Filament: login centralizzato in HR (preambolo) e regole di
 * User::canAccessPanel().
 */

test('la pagina di login di HR è raggiungibile', function () {
    $this->get('/hr/login')->assertOk();
});

test('un ospite che apre un panel qualsiasi viene mandato al login di HR', function (string $path) {
    $this->get($path)->assertRedirect('/hr/login');
})->with(['/hr', '/kaffettino']);

test('le rotte "auth" fuori dai panel mandano gli ospiti al login di HR', function () {
    // Rotta stub di un modulo non ancora sviluppato, protetta da "auth"
    $this->get('/drives')->assertRedirect('/hr/login');
});

test('uno studente attivo entra in HR ma non in Kaffettino', function () {
    $student = User::factory()->create();

    $this->actingAs($student)->get('/hr')->assertOk();
    $this->actingAs($student)->get('/kaffettino')->assertForbidden();
});

test('lo staff attivo entra in Kaffettino', function () {
    $this->actingAs(User::factory()->staff()->withStaffProfile()->create())->get('/kaffettino')->assertOk();
});

test('lo staff ancora in attesa non entra in Kaffettino', function () {
    $this->actingAs(User::factory()->staff()->pending()->create())->get('/kaffettino')->assertForbidden();
});

test('un utente bannato non entra in HR', function () {
    $banned = User::factory()->create();
    $banned->status = UserStatus::Banned;
    $banned->save();

    $this->actingAs($banned)->get('/hr')->assertForbidden();
});

test('un utente con la mail non verificata viene fermato alla verifica', function () {
    $this->actingAs(User::factory()->unverified()->create())
        ->get('/hr')
        ->assertRedirect('/hr/email-verification/prompt');
});
