<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Ingegneria '.fake()->unique()->word().' '.fake()->unique()->numerify('###'),
            'department_id' => Department::factory(),
            'auletta_id' => null,
        ];
    }
}
