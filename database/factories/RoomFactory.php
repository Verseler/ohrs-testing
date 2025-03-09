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
            'bed_price_rate' => 200,
            'eligible_gender' => 'any',//$this->faker->randomElement(['any', 'male', 'female']),
            'office_id' => 1, //!temporary
        ];
    }
}
