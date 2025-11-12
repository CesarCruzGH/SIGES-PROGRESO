<?php

namespace Database\Seeders;

use App\Models\ClinicSchedule;
use App\Models\Service;
use App\Models\User;
use App\Enums\Shift;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class ClinicScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $clinicNames = ['Consultorio A','Consultorio B','Consultorio C','Consultorio D'];
        $dates = [Carbon::yesterday()->toDateString(), Carbon::today()->toDateString(), Carbon::tomorrow()->toDateString()];
        $shifts = [Shift::MATUTINO, Shift::VESPERTINO];

        $doctorIds = User::query()->where('role', \App\Enums\UserRole::MEDICO_GENERAL->value)->pluck('id');
        if ($doctorIds->isEmpty()) {
            $doctorIds = User::query()->pluck('id');
        }
        $serviceIds = Service::query()->pluck('id');

        foreach ($clinicNames as $clinic) {
            foreach ($dates as $date) {
                foreach ($shifts as $shift) {
                    $isOpen = (bool) random_int(0, 1);
                    ClinicSchedule::query()->create([
                        'clinic_name' => $clinic,
                        'user_id' => $doctorIds->random(),
                        'service_id' => $serviceIds->random(),
                        'shift' => $shift,
                        'date' => $date,
                        'is_active' => true,
                        'is_shift_open' => $isOpen,
                        'shift_opened_at' => $isOpen ? now() : null,
                        'opened_by' => $isOpen ? Arr::random($doctorIds->all()) : null,
                    ]);
                }
            }
        }
    }
}
