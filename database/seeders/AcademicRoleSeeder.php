<?php

namespace Database\Seeders;

use App\Models\AcademicRole;
use Illuminate\Database\Seeder;

/**
 * Cariche istituzionali iniziali. Idempotente: si può rilanciare senza creare duplicati.
 * Altre cariche si aggiungono dall'interfaccia (non sono un enum di proposito).
 */
class AcademicRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['CDD', 'CCS', 'CDA', 'CDA-ERSU', 'CNSU', 'CSU'] as $role) {
            AcademicRole::firstOrCreate(['role' => $role]);
        }
    }
}
