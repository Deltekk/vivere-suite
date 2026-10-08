<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Liceo Scientifico', 'Liceo Classico', 'ITIS', 'Istituto Tecnico'])
                .' '.fake()->unique()->lastName(),
            'city' => fake()->city(),
            'lat' => fake()->optional()->latitude(37.5, 38.3),
            'lon' => fake()->optional()->longitude(12.4, 15.6),
        ];
    }
}
