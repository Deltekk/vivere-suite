<?php

use App\Models\Auletta;
use App\Models\Course;
use App\Models\User;
use App\Models\UserAcademicRole;

/*
 * Le pagine di gestione del panel HR si aprono senza errori (test di "fumo"): un errore
 * in una colonna, in un form o in una relazione farebbe fallire il rendering.
 */

beforeEach(function () {
    // Con il profilo staff, altrimenti verrebbe mandato a completarlo
    $this->admin = User::factory()->superAdmin()->withStaffProfile()->create();
});

test('le pagine elenco si aprono', function (string $path) {
    User::factory()->count(3)->create();
    User::factory()->pending()->create();
    Auletta::factory()->create();
    UserAcademicRole::factory()->create();

    $this->actingAs($this->admin)->get("/hr/{$path}")->assertOk();
})->with([
    'utenti' => 'utenti',
    'aulette' => 'aulette',
    'corsi' => 'courses',
    'edifici' => 'buildings',
    'macroaree' => 'macroareas',
    'dipartimenti' => 'departments',
    'aule' => 'classrooms',
    'scuole' => 'schools',
    'cariche' => 'academic-roles',
]);

test('le pagine di creazione si aprono', function (string $path) {
    $this->actingAs($this->admin)->get("/hr/{$path}/create")->assertOk();
})->with(['utenti', 'aulette', 'courses']);

test('la scheda e la modifica di un utente si aprono', function () {
    $user = User::factory()->staff()->withStaffProfile()->create();
    UserAcademicRole::factory()->for($user)->create();

    $this->actingAs($this->admin)->get("/hr/utenti/{$user->id}")->assertOk()->assertSee($user->username);
    $this->actingAs($this->admin)->get("/hr/utenti/{$user->id}/edit")->assertOk();
});

test('la modifica di auletta e corso si apre', function () {
    $this->actingAs($this->admin)->get('/hr/aulette/'.Auletta::factory()->create()->id.'/edit')->assertOk();
    $this->actingAs($this->admin)->get('/hr/courses/'.Course::factory()->create()->id.'/edit')->assertOk();
});

test('le pagine di comunicazione e la dashboard si aprono per un admin', function (string $path) {
    $this->actingAs($this->admin)->get("/hr/{$path}")->assertOk();
})->with(['', 'invia-notifica', 'changelogs', 'audit-log', 'profile']);

test('la pagina di registrazione si apre per un ospite', function () {
    $this->get('/hr/register')->assertOk()->assertSee('Per quali servizi vuoi ricevere email?');
});

test('le pagine del percorso dell\'account si aprono', function () {
    $this->actingAs(User::factory()->staff()->create())->get('/hr/profilo-staff')->assertOk();
    $this->actingAs(User::factory()->create())->get('/hr/conferma-anno')->assertOk();
});

test('la pagina per inviare notifiche è vietata allo staff', function () {
    $this->actingAs(User::factory()->staff()->withStaffProfile()->create())->get('/hr/invia-notifica')->assertForbidden();
});
