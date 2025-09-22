<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tutor>
 */
class TutorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'relationship' => $this->faker->randomElement(['Madre', 'Padre', 'Tutor Legal']),
            'phone_number' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),        ];
    }
}
