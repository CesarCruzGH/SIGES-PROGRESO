<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
//cases
use App\Models\Tutor;
use App\Enums\PatientType;
use App\Enums\Shift;
use App\Enums\VisitType;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sex = $this->faker->randomElement(['Masculino', 'Femenino']);
        // Por defecto, crea un paciente adulto
        $dateOfBirth = $this->faker->dateTimeBetween('-80 years', '-18 years');
        $tutorId = null;
        return [
            'full_name' => $this->faker->name($sex === 'Masculino' ? 'male' : 'female'),
            'date_of_birth' => $dateOfBirth,
            'sex' => $sex,
            'curp' => $this->faker->unique()->numerify('##################'), // CURP falso simple
            'locality' => 'Progreso', // Localidad por defecto
            'patient_type' => $this->faker->randomElement(PatientType::cases()),
            'shift' => $this->faker->randomElement(Shift::cases()),
            'visit_type' => $this->faker->randomElement(VisitType::cases()),
            'has_disability' => $this->faker->boolean(10), // 10% de probabilidad de tener discapacidad
            'tutor_id' => $tutorId,
        ];
    }
    /**
     * Define un estado para crear un paciente menor de edad.
     */
    public function isMinor(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'date_of_birth' => $this->faker->dateTimeBetween('-17 years', '-1 day'),
                // Crea un nuevo tutor o usa uno existente, y le asigna el ID al paciente.
                'tutor_id' => Tutor::factory(),
            ];
        });
    }
}