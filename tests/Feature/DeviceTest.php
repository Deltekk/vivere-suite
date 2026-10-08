<?php

use App\Models\Device;
use Illuminate\Support\Facades\DB;

/*
 * Dispositivi embedded (core.devices): token API e codice della modalità amministratore.
 */

test('del token API si salva solo l\'hash', function () {
    $device = Device::factory()->create();
    $token = $device->issueToken();
    $device->save();

    expect(DB::table('core.devices')->where('id', $device->id)->value('token_hash'))
        ->toBe(hash('sha256', $token))
        ->not->toBe($token);
});

test('il codice admin è cifrato nel DB ma rileggibile dagli admin', function () {
    $device = Device::factory()->create();
    $device->changeAdminCode('48213705');
    $device->save();

    expect(DB::table('core.devices')->where('id', $device->id)->value('admin_code'))->not->toContain('48213705')
        ->and($device->fresh()->admin_code)->toBe('48213705');
});

test('il dispositivo riceve un hash del codice diverso da quello degli altri dispositivi', function () {
    [$first, $second] = Device::factory()->count(2)->create()->all();
    $first->changeAdminCode('1234');
    $second->changeAdminCode('1234');

    expect($first->adminCodeHashForDevice())->not->toBe($second->adminCodeHashForDevice());
});

test('un nuovo codice resta "in attesa" finché il dispositivo non si sincronizza', function () {
    $device = Device::factory()->create();
    $device->changeAdminCode('1234');

    expect($device->isAdminCodeSyncPending())->toBeTrue();

    $this->travel(1)->minutes();
    $device->config_synced_at = now();

    expect($device->isAdminCodeSyncPending())->toBeFalse();
});
