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
        $schedules = ClinicSchedule::query()->where('is_active', true)->where('is_shift_open', true)->get();
        if ($schedules->isEmpty()) {
            $schedules = ClinicSchedule::query()->where('is_active', true)->get();
        }
        $records = MedicalRecord::query()->pluck('id');
        $statuses = [
            AppointmentStatus::PENDING->value,
            AppointmentStatus::IN_PROGRESS->value,
            AppointmentStatus::COMPLETED->value,
            AppointmentStatus::CANCELLED->value,
        ];

        if ($schedules->isEmpty() || $records->isEmpty()) {
            return;
        }
        $count = 50;
        Appointment::withoutEvents(function () use ($count, $schedules, $records, $statuses) {
            for ($i = 0; $i < $count; $i++) {
                $schedule = $schedules->random();
                $recordId = $records->random();
                Appointment::factory()->create([
                    'ticket_number' => \App\Models\Appointment::generateWalkInTicket(),
                    'medical_record_id' => $recordId,
                    'clinic_schedule_id' => $schedule->id,
                    'service_id' => $schedule->service_id,
                    'doctor_id' => $schedule->user_id,
                    'date' => $schedule->date,
                    'shift' => $schedule->shift->value,
                    'status' => fake()->randomElement($statuses),
                ]);
            }
        });
    }
}
