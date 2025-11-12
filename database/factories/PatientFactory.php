<?php

namespace Database\Factories;

use App\Enums\Locality;
use App\Enums\PatientType;
use App\Models\Patient;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        $sex = $this->faker->randomElement(['M','F']);
        return [
            'tutor_id' => null,
            'full_name' => $this->faker->name(),
            'date_of_birth' => $this->faker->dateTimeBetween('-90 years', '-1 year')->format('Y-m-d'),
            'sex' => $sex,
            'curp' => $this->faker->optional()->regexify('[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}'),
            'locality' => $this->faker->randomElement(Locality::cases()),
            'contact_phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'has_disability' => $this->faker->boolean(10),
            'disability_details' => $this->faker->optional()->sentence(6),
            'status' => $this->faker->randomElement(['active','pending_review']),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Patient $patient) {
            $type = Arr::random(PatientType::cases());
            $patient->medicalRecord()->update(['patient_type' => $type]);
        });
    }
}
