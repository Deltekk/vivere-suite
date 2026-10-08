<?php

namespace Database\Seeders;

use App\Models\AcademicRole;
use App\Models\User;
use App\Models\UserAcademicRole;
use Illuminate\Database\Seeder;

/**
 * Mandati istituzionali finti (attuali e scaduti), solo per lo sviluppo locale.
 */
class UserAcademicRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = AcademicRole::all();

        User::query()->inRandomOrder()->limit(6)->get()->each(function (User $user) use ($roles): void {
            UserAcademicRole::factory()->for($user)->for($roles->random())->create();
        });

        UserAcademicRole::factory()->expired()->for(User::query()->inRandomOrder()->firstOrFail())->for($roles->random())->create();
    }
}
