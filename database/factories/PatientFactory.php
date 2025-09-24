<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
//cases
use App\Models\Tutor;
use App\Enums\Locality;
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
        return [
            'tutor_id' => null,
            'full_name' => $this->faker->name($sex === 'Masculino' ? 'male' : 'female'),
            'date_of_birth' => $dateOfBirth,
            'sex' => $sex,
            'curp' => $this->faker->unique()->numerify('##################'), // CURP falso simple
            'locality' => $this->faker->randomElement(Locality::cases()),
            'contact_phone' => $this->faker->e164PhoneNumber(),
            'address' => $this->faker->address(),
            'has_disability' => $this->faker->boolean(10), // 10% de probabilidad de tener discapacidad
            'disability_details' => $this->faker->optional()->sentence(),
            'status' => 'active',
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