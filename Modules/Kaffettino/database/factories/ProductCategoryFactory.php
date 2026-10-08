<?php

namespace Modules\Kaffettino\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Kaffettino\Models\ProductCategory;

/**
 * @extends Factory<ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Caffè', 'Snack', 'Bibite', 'Dolci', 'Tè e tisane', 'Salati']).' '.fake()->unique()->numerify('###'),
        ];
    }
}
