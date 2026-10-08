<?php

namespace Modules\Kaffettino\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Kaffettino\Models\Card;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    protected $model = Card::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uid' => fake()->unique()->regexify('[0-9A-F]{8}'), // UID NFC a 4 byte in esadecimale
            'assigned_at' => now(),
            'user_id' => User::factory()->staff(),
            'assigned_by' => User::factory()->admin(),
        ];
    }

    /**
     * Card smarrita o sostituita.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => ['revoked_at' => now()]);
    }
}
