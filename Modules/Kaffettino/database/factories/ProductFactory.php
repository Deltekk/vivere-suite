<?php

namespace Modules\Kaffettino\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Kaffettino\Models\Product;
use Modules\Kaffettino\Models\ProductCategory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Caffè', 'Cappuccino', 'Acqua', 'Succo', 'Cornetto', 'Patatine']).' '.fake()->word(),
            'category_id' => ProductCategory::factory(),
        ];
    }
}
