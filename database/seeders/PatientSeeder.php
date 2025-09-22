<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        // Crea 50 pacientes adultos (sin tutor)
        Patient::factory()->count(50)->create();

        // Crea 25 pacientes menores.
        // Por cada uno, la factory automáticamente creará un nuevo tutor
        // y lo asignará.
        Patient::factory()->count(25)->isMinor()->create();
    }
}
