<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Professore dell'ateneo, popolato dal job di scraping (core.professors).
 *
 * @property string $id
 * @property string $full_name
 * @property string $normalized_name
 * @property CarbonImmutable $scraped_at
 */
#[Table(name: 'core.professors', timestamps: false)]
#[Fillable(['full_name', 'normalized_name', 'scraped_at'])]
class Professor extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'scraped_at' => 'datetime',
        ];
    }
}
