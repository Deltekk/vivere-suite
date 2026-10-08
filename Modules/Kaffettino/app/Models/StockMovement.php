<?php

namespace Modules\Kaffettino\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Kaffettino\Enums\StockReason;

/**
 * Carico o rettifica di magazzino (kaffettino.stock_movements). Append-only (trigger DB).
 *
 * @property string $id
 * @property int $quantity
 * @property StockReason $reason
 * @property int|null $unit_cost_cents
 * @property string|null $note
 * @property string $warehouse_product_id
 * @property string $performed_by
 * @property CarbonImmutable $created_at
 */
#[Table('kaffettino.stock_movements')]
#[Fillable(['quantity', 'reason', 'unit_cost_cents', 'note', 'warehouse_product_id', 'performed_by'])]
class StockMovement extends Model
{
    use HasUuids;

    /** La tabella ha solo created_at. */
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reason' => StockReason::class,
            'unit_cost_cents' => 'integer',
        ];
    }

    /** @return BelongsTo<WarehouseProduct, $this> */
    public function warehouseProduct(): BelongsTo
    {
        return $this->belongsTo(WarehouseProduct::class);
    }

    /** @return BelongsTo<User, $this> */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
