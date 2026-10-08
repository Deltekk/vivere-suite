<?php

namespace Modules\Kaffettino\Models;

use App\Models\Auletta;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un'auletta che vende con Kaffettino e il suo magazzino (kaffettino.auletta_warehouse).
 * Estende core.aulette (1:1): la chiave primaria è l'id dell'auletta.
 *
 * @property string $auletta_id
 * @property CarbonImmutable|null $delisted_at
 * @property string $warehouse_id
 */
#[Table(name: 'kaffettino.auletta_warehouse', key: 'auletta_id', keyType: 'string', incrementing: false)]
#[Fillable(['auletta_id', 'delisted_at', 'warehouse_id'])]
class AulettaWarehouse extends Model
{
    protected function casts(): array
    {
        return [
            'delisted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Auletta, $this> */
    public function auletta(): BelongsTo
    {
        return $this->belongsTo(Auletta::class);
    }

    /** @return BelongsTo<Warehouse, $this> */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
