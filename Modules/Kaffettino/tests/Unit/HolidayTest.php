<?php

use Carbon\CarbonImmutable;
use Modules\Kaffettino\Enums\Holiday;

/*
 * Festività per le immagini "buongiornissimo kaffè!" (Modules\Kaffettino\Enums\Holiday).
 */

test('Pasqua e Pasquetta cadono nelle date corrette', function (int $year, string $easter) {
    expect(Holiday::Easter->dateIn($year)->toDateString())->toBe($easter)
        ->and(Holiday::EasterMonday->dateIn($year)->toDateString())->toBe(CarbonImmutable::parse($easter)->addDay()->toDateString());
})->with([
    [2024, '2024-03-31'],
    [2025, '2025-04-20'],
    [2026, '2026-04-05'],
    [2027, '2027-03-28'],
    [2038, '2038-04-25'],
]);

test('riconosce le festività a data fissa', function (string $date, Holiday $holiday) {
    expect(Holiday::on(CarbonImmutable::parse($date)))->toBe([$holiday]);
})->with([
    ['2026-01-01', Holiday::NewYear],
    ['2026-06-02', Holiday::RepublicDay],
    ['2026-09-11', Holiday::RosoneBirthday],
    ['2026-12-26', Holiday::SaintStephen],
]);

test('San Francesco è festa solo dal 2026', function () {
    expect(Holiday::on(CarbonImmutable::parse('2025-10-04')))->toBe([])
        ->and(Holiday::on(CarbonImmutable::parse('2026-10-04')))->toBe([Holiday::SaintFrancis]);
});

test('restituisce tutte le festività che coincidono', function () {
    // Nel 2038 Pasqua cade il 25 aprile
    expect(Holiday::on(CarbonImmutable::parse('2038-04-25')))->toBe([Holiday::Easter, Holiday::LiberationDay]);
});

test('un giorno qualunque non è festa', function () {
    expect(Holiday::on(CarbonImmutable::parse('2026-10-09')))->toBe([]);
});
