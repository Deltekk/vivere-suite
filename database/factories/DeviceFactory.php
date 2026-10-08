<?php

namespace Database\Factories;

use App\Enums\DeviceType;
use App\Models\Auletta;
use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'ESP32 '.fake()->unique()->bothify('??-##'),
            'type' => DeviceType::KaffettinoReader,
            'token_hash' => Device::hashToken(Str::random(48)),
            'auletta_id' => Auletta::factory(),
        ];
    }
}
