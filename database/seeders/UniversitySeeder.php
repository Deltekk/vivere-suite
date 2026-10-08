<?php

namespace Database\Seeders;

use App\Models\Auletta;
use App\Models\Building;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Department;
use App\Models\Macroarea;
use Illuminate\Database\Seeder;

/**
 * Struttura dell'ateneo DI ESEMPIO, solo per lo sviluppo locale: nomi plausibili ma non
 * ufficiali. I dati veri si inseriscono dal panel HR (o, per le aule, con lo scraping di Aule Libere).
 */
class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        $engineering = Macroarea::firstOrCreate(['name' => 'Ingegneria']);
        $economics = Macroarea::firstOrCreate(['name' => 'Economia']);

        $engineeringDepartment = Department::firstOrCreate(['name' => 'Dipartimento di Ingegneria'], ['macroarea_id' => $engineering->id]);
        $economicsDepartment = Department::firstOrCreate(['name' => 'Dipartimento di Scienze Economiche'], ['macroarea_id' => $economics->id]);

        $building8 = Building::firstOrCreate(['number' => 8], ['name' => 'Edificio 8']);
        $building9 = Building::firstOrCreate(['number' => 9], ['name' => 'Edificio 9']);
        $building13 = Building::firstOrCreate(['number' => 13], ['name' => 'Edificio 13']);

        $secondFloor = Auletta::firstOrCreate(['name' => 'Auletta 2° piano'], ['building_id' => $building8->id]);
        $thirdFloor = Auletta::firstOrCreate(['name' => 'Auletta 3° piano'], ['building_id' => $building8->id]);
        $deim = Auletta::firstOrCreate(['name' => 'Auletta DEIM'], ['building_id' => $building9->id]);
        $economicsAuletta = Auletta::firstOrCreate(['name' => 'Auletta Economia'], ['building_id' => $building13->id]);

        $courses = [
            'Ingegneria Informatica' => [$engineeringDepartment, $secondFloor],
            'Ingegneria Elettronica' => [$engineeringDepartment, $thirdFloor],
            'Ingegneria Gestionale' => [$engineeringDepartment, $deim],
            'Ingegneria Civile' => [$engineeringDepartment, null],
            'Economia e Commercio' => [$economicsDepartment, $economicsAuletta],
        ];

        foreach ($courses as $name => [$department, $auletta]) {
            Course::firstOrCreate(['name' => $name], ['department_id' => $department->id, 'auletta_id' => $auletta?->id]);
        }

        foreach ([['F120', 'Aula Rubino', $building8], ['F220', null, $building8], ['A1', null, $building9]] as [$code, $name, $building]) {
            Classroom::firstOrCreate(['building_id' => $building->id, 'code' => $code], ['name' => $name, 'department_id' => $engineeringDepartment->id]);
        }
    }
}
