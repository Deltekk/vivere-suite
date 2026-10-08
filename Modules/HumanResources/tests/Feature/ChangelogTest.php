<?php

use App\Livewire\ChangelogModal;
use App\Models\Changelog;
use App\Models\User;
use Livewire\Livewire;

/*
 * Changelog al primo accesso utile in ogni piattaforma (preambolo).
 */

test('le novità non ancora viste compaiono una volta sola', function () {
    $user = User::factory()->create();
    Changelog::create(['service' => 'humanresources', 'title' => 'Nuova registrazione', 'body' => 'Ora puoi **registrarti**.', 'published_at' => now()->subDay()]);
    Changelog::create(['service' => 'humanresources', 'title' => 'Futura', 'body' => 'Non ancora', 'published_at' => now()->addDay()]);
    Changelog::create(['service' => 'kaffettino', 'title' => 'Altro panel', 'body' => 'No', 'published_at' => now()->subDay()]);

    Livewire::actingAs($user)->test(ChangelogModal::class, ['panelId' => 'hr'])
        ->assertSee('Nuova registrazione')
        ->assertDontSee('Futura')
        ->assertDontSee('Altro panel')
        ->call('markAsSeen');

    Livewire::actingAs($user)->test(ChangelogModal::class, ['panelId' => 'hr'])->assertDontSee('Nuova registrazione');
});
