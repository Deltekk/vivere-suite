<?php

namespace Modules\Kaffettino\Database\Factories;

use App\Models\Auletta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Kaffettino\Models\Account;
use Modules\Kaffettino\Models\Warehouse;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Di default: conto personale di uno staffer, a saldo zero.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'balance_cents' => 0,
            'user_id' => User::factory()->staff(),
            'auletta_id' => null,
            'warehouse_id' => Warehouse::factory(),
            'opened_by' => User::factory()->admin(),
        ];
    }

    /**
     * Conto dell'auletta (omaggi agli ospiti).
     */
    public function forAuletta(?Auletta $auletta = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'auletta_id' => $auletta ?? Auletta::factory(),
        ]);
    }
}
