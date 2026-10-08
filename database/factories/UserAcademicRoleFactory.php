<?php

namespace Database\Factories;

use App\Models\AcademicRole;
use App\Models\User;
use App\Models\UserAcademicRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserAcademicRole>
 */
class UserAcademicRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Di default: mandato biennale in corso.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'started_at' => $startedAt,
            'expires_at' => (clone $startedAt)->modify('+2 years'),
            'user_id' => User::factory(),
            'academic_role_id' => AcademicRole::factory(),
        ];
    }

    /**
     * Mandato già scaduto e non rinnovato.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'started_at' => now()->subYears(3),
            'expires_at' => now()->subYear(),
        ]);
    }
}
