<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\ClinicSchedule;
use App\Models\MedicalRecord;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $openSchedules = ClinicSchedule::query()->where('is_active', true)->where('is_shift_open', true)->get();
        $records = MedicalRecord::query()->pluck('id');
        $statuses = [
            AppointmentStatus::PENDING->value,
            AppointmentStatus::IN_PROGRESS->value,
            AppointmentStatus::COMPLETED->value,
            AppointmentStatus::CANCELLED->value,
        ];

        $count = 90;
        for ($i = 0; $i < $count; $i++) {
            $schedule = $openSchedules->random();
            $recordId = $records->random();
            Appointment::factory()->create([
                'medical_record_id' => $recordId,
                'clinic_schedule_id' => $schedule->id,
                'service_id' => $schedule->service_id,
                'doctor_id' => $schedule->user_id,
                'date' => $schedule->date,
                'shift' => $schedule->shift->value,
                'status' => fake()->randomElement($statuses),
            ]);
        }
    }
}

