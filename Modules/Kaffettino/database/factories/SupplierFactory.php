<?php

namespace Modules\Kaffettino\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Kaffettino\Models\Supplier;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->address(),
            'phone_number' => fake()->optional()->numerify('+39 091 ### ####'),
            'email' => fake()->optional()->companyEmail(),
        ];
    }
}
