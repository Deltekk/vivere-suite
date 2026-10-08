<?php

namespace Database\Factories;

use App\Models\Macroarea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Macroarea>
 */
class MacroareaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word().' '.fake()->unique()->numerify('###'),
        ];
    }
}
