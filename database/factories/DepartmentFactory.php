<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Macroarea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Dipartimento di '.fake()->unique()->word().' '.fake()->unique()->numerify('###'),
            'macroarea_id' => Macroarea::factory(),
        ];
    }
}
