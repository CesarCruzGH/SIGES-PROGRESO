<?php

namespace Database\Seeders;

use App\Models\MedicalRecord;
use App\Models\NursingAssessment;
use Illuminate\Database\Seeder;

class NursingAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $records = MedicalRecord::query()->inRandomOrder()->take(ceil(MedicalRecord::query()->count() * 0.3))->get();
        foreach ($records as $record) {
            NursingAssessment::factory()->create([
                'medical_record_id' => $record->id,
            ]);
        }
    }
}

