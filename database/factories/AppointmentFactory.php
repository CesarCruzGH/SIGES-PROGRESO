<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medical_record_id' => \App\Models\MedicalRecord::factory(),
            'service_id' => 1,
            'doctor_id' => null,
            'ticket_number' => strtoupper($this->faker->bothify('TKT-######')),
            'shift' => $this->faker->randomElement(['matutino', 'vespertino', 'nocturno']),
            'visit_type' => $this->faker->randomElement(['primera_vez', 'subsecuente']),
            'clinic_room_number' => (string) $this->faker->numberBetween(1, 20),
            'reason_for_visit' => $this->faker->sentence(6),
            'notes' => $this->faker->optional()->sentence(),
            'status' => 'pending',
        ];
    }
}


