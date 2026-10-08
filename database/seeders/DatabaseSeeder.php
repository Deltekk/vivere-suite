<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * - Dati di riferimento e super admin: girano in ogni ambiente, anche in produzione.
     * - Dati finti: solo in locale, per avere qualcosa da vedere nei panel.
     */
    public function run(): void
    {
        $this->call([
            AcademicRoleSeeder::class,
            SuperAdminSeeder::class,
        ]);

        if (app()->isLocal()) {
            $this->call([
                UniversitySeeder::class,
                SchoolSeeder::class,
                StaffProfileSeeder::class,
                UserAcademicRoleSeeder::class,
            ]);
        }
    }
}
