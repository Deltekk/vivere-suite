<?php

namespace Modules\Kaffettino\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Kaffettino\Models\Product;
use Modules\Kaffettino\Models\Supplier;
use Modules\Kaffettino\Models\Warehouse;
use Modules\Kaffettino\Models\WarehouseProduct;

/**
 * @extends Factory<WarehouseProduct>
 */
class WarehouseProductFactory extends Factory
{
    protected $model = WarehouseProduct::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'price_cents' => fake()->randomElement([50, 80, 100, 150]),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'low_stock_threshold' => 10,
            'warehouse_id' => Warehouse::factory(),
            'product_id' => Product::factory(),
            'supplier_id' => Supplier::factory(),
        ];
    }
}
