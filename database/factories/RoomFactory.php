<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'eligible_gender' => $this->faker->randomElement(['any', 'male', 'female']),
            'status' => $this->faker->randomElement(['available', 'fully_occupied', 'maintenance'])
        ];
    }
}
