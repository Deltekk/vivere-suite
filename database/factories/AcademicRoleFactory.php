<?php

namespace Database\Factories;

use App\Models\AcademicRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicRole>
 */
class AcademicRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role' => fake()->unique()->lexify('CARICA-????'),
        ];
    }
}
