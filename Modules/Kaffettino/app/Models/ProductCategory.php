<?php

namespace Modules\Kaffettino\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Kaffettino\Database\Factories\ProductCategoryFactory;

/**
 * Tipologia di prodotto (kaffettino.product_categories): Caffè, Snack, Bibite, ...
 *
 * @property string $id
 * @property string $name
 */
#[Table('kaffettino.product_categories')]
#[Fillable(['name'])]
#[UseFactory(ProductCategoryFactory::class)]
class ProductCategory extends Model
{
    /** @use HasFactory<ProductCategoryFactory> */
    use HasFactory, HasUuids;

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
