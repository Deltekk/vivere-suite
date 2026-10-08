<?php

namespace Database\Factories;

use App\Models\Auletta;
use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auletta>
 */
class AulettaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Auletta '.fake()->unique()->word(),
            'building_id' => Building::factory(),
        ];
    }
}
