<?php

use App\Models\Professor;
use App\Models\User;
use Modules\HumanResources\Actions\SyncProfessors;
use Modules\HumanResources\Support\RegistrationWarnings;

/*
 * Elenco dei professori e avviso allo staff in caso di omonimia (HR 2.2.1, D12).
 */

test('chi ha il nome di un professore riceve un avviso, in qualunque ordine sia scritto il nome', function () {
    app(SyncProfessors::class)->handle(['ROSSI Mario', 'Anna Bianchi']);

    expect(RegistrationWarnings::matchesProfessor(User::factory()->make(['name' => 'Mario', 'surname' => 'Rossi'])))->toBeTrue()
        ->and(RegistrationWarnings::matchesProfessor(User::factory()->make(['name' => 'Luca', 'surname' => 'Neri'])))->toBeFalse();
});

test('una fonte vuota non cancella l\'elenco esistente', function () {
    app(SyncProfessors::class)->handle(['Mario Rossi']);

    expect(app(SyncProfessors::class)->handle([]))->toBe(0)
        ->and(Professor::count())->toBe(1);
});

test('l\'elenco si importa anche da file', function () {
    $file = tempnam(sys_get_temp_dir(), 'prof');
    file_put_contents($file, "Mario Rossi\nAnna Bianchi\n\nMario Rossi\n");

    $this->artisan('hr:import-professors', ['file' => $file])->assertSuccessful();

    expect(Professor::count())->toBe(2);
    unlink($file);
});
