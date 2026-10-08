<?php

namespace Modules\Kaffettino\Models;

use App\Models\Auletta;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Kaffettino\Database\Factories\AccountFactory;

/**
 * Conto Kaffettino (kaffettino.accounts), intestato a una persona OPPURE a un'auletta (vincolo DB).
 *
 * - Conto personale: uno per magazzino, solo per lo staff (D8), utilizzabile solo con una card attiva,
 *   con il debito massimo del magazzino (D6).
 * - Conto dell'auletta: raccoglie gli omaggi agli ospiti; nessun limite di debito, niente mail sui debiti.
 *
 * Il saldo NON si modifica mai a mano: lo aggiorna solo il codice che registra un movimento,
 * nella stessa transazione DB e con lock sulla riga (lockForUpdate).
 *
 * @property string $id
 * @property int $balance_cents
 * @property CarbonImmutable|null $closed_at
 * @property string|null $user_id
 * @property string|null $auletta_id
 * @property string $warehouse_id
 * @property string $opened_by
 */
#[Table('kaffettino.accounts')]
#[Fillable(['closed_at', 'user_id', 'auletta_id', 'warehouse_id', 'opened_by'])]
#[UseFactory(AccountFactory::class)]
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'balance_cents' => 'integer',
            'closed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    /** @return HasMany<Transaction, $this> */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Vero per il conto dell'auletta (omaggi agli ospiti).
     */
    public function isAulettaAccount(): bool
    {
        return $this->auletta_id !== null;
    }
}
