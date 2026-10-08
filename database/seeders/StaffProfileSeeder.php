<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Staff e admin finti con il loro profilo, più studenti attivi e in attesa, solo per lo sviluppo
 * locale. Usa i corsi di UniversitySeeder.
 * Tutti gli utenti finti hanno password "password".
 */
class StaffProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = School::all();
        $courses = Course::all();
        $course = fn () => $courses->random()->id;

        // La scuola di provenienza è obbligatoria per lo staff (piattaforma Orientamento)
        User::factory()->staff()->withStaffProfile()->count(5)->create(['school_id' => fn () => $schools->random()->id, 'course_id' => $course]);
        $admins = User::factory()->admin()->withStaffProfile()->count(2)->create(['school_id' => fn () => $schools->random()->id, 'course_id' => $course]);

        // Ogni corso tranne uno ha un admin: il corso scoperto compare nella dashboard HR
        $courses->skip(1)->each(fn (Course $item) => $item->administrators()->attach($admins->random()));

        User::factory()->count(20)->create(['course_id' => $course]);
        User::factory()->pending()->count(5)->create(['course_id' => $course]);
        User::factory()->highSchool()->pending()->count(3)->create();
    }
}
