<?php

namespace Database\Factories;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffProfile>
 */
class StaffProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Stessa forma di un codice fiscale (16 caratteri), senza pretesa di validità
            'tax_code' => fake()->unique()->regexify('[A-Z]{6}[0-9]{2}[A-EHLMPR-T][0-9]{2}[A-Z][0-9]{3}[A-Z]'),
            'birth_city' => fake()->city(),
            'birth_province' => fake()->randomElement(['PA', 'AG', 'CL', 'CT', 'EN', 'ME', 'RG', 'SR', 'TP']),
            'birth_country' => 'Italia',
            'user_id' => User::factory()->staff(),
        ];
    }
}
