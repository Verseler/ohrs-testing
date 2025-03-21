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
        static $order = 1;

        return [
            'name' => 'Room ' . $order++,
            'eligible_gender' => 'any',//$this->faker->randomElement(['any', 'male', 'female']),
            'office_id' => 175, //! region 10 - REO
        ];
    }
}
