<?php

namespace Modules\Kaffettino\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Kaffettino\Models\Warehouse;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Magazzino '.fake()->unique()->word(),
            'max_debt_cents' => 150, // 3 caffè da 0,50 €
        ];
    }
}
