<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Building>
 */
class BuildingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 999);

        return [
            'name' => "Edificio {$number}",
            'number' => $number,
            'lat' => fake()->optional()->latitude(38.10, 38.11),
            'lon' => fake()->optional()->longitude(13.34, 13.36),
        ];
    }
}
