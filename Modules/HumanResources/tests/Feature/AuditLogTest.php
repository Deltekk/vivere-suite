<?php

use App\Models\Activity;
use App\Models\Building;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/*
 * Audit log (preambolo): ogni modifica viene registrata, su DB e su file, e non si può alterare.
 */

test('le modifiche ai dati finiscono nell\'audit log senza i segreti', function () {
    $user = User::factory()->create();
    $user->update(['phone_number' => '+39 320 1112233', 'password' => 'Una-Nuova-Password-1!']);

    $activity = Activity::where('subject_id', $user->id)->where('event', 'updated')->latest()->first();

    expect($activity->subject_type)->toBe('user')
        ->and($activity->attribute_changes['attributes'])->toHaveKey('phone_number')
        ->and($activity->attribute_changes['attributes'])->not->toHaveKey('password');
});

test('ogni voce viene scritta anche sul file di log "audit"', function () {
    Log::shouldReceive('channel')->with('audit')->atLeast()->once()->andReturnSelf();
    Log::shouldReceive('info')->atLeast()->once();

    Building::factory()->create();
});

test('le voci dell\'audit log non si possono modificare', function () {
    Building::factory()->create();

    DB::table('core.activity_log')->update(['description' => 'manomesso']);
})->throws(QueryException::class, 'append-only');

test('le voci recenti non si possono cancellare, quelle oltre la conservazione sì', function () {
    Building::factory()->create();
    $activity = Activity::first();

    expect(fn () => DB::transaction(fn () => DB::table('core.activity_log')->where('id', $activity->id)->delete()))
        ->toThrow(QueryException::class);

    // Simula una voce vecchia di 3 anni (inserita direttamente, il trigger blocca solo UPDATE e DELETE)
    $old = (string) Str::uuid7();
    DB::table('core.activity_log')->insert(['id' => $old, 'description' => 'vecchia', 'created_at' => now()->subYears(3), 'updated_at' => now()->subYears(3)]);

    expect(DB::table('core.activity_log')->where('id', $old)->delete())->toBe(1);
});
