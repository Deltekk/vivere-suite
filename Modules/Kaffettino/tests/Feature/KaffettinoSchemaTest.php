<?php

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Kaffettino\Enums\TransactionChannel;
use Modules\Kaffettino\Enums\TransactionType;
use Modules\Kaffettino\Models\Account;
use Modules\Kaffettino\Models\BirthdayGift;
use Modules\Kaffettino\Models\Card;
use Modules\Kaffettino\Models\Coupon;
use Modules\Kaffettino\Models\Transaction;
use Modules\Kaffettino\Models\WarehouseProduct;

/*
 * Vincoli di integrità dello schema kaffettino: le regole decise con l'associazione
 * sono garantite dal database, non solo dal codice (vedi CLAUDE.md).
 */

/**
 * Crea un movimento valido sul conto indicato (i parametri sovrascrivono i default).
 *
 * @param  array<string, mixed>  $attributes
 */
function makeTransaction(Account $account, array $attributes = []): Transaction
{
    return Transaction::create([
        'type' => TransactionType::TopUp,
        'channel' => TransactionChannel::Web,
        'amount_cents' => 500,
        'balance_after_cents' => 500,
        'occurred_at' => now(),
        'account_id' => $account->id,
        'warehouse_id' => $account->warehouse_id,
        ...$attributes,
    ]);
}

test('le tabelle del modulo stanno nello schema kaffettino', function () {
    $account = Account::factory()->create();

    expect(DB::table('kaffettino.accounts')->where('id', $account->id)->exists())->toBeTrue();
});

test('le scorte non possono scendere sotto zero', function () {
    $product = WarehouseProduct::factory()->create(['stock_quantity' => 1]);

    $product->decrement('stock_quantity', 2);
})->throws(QueryException::class);

test('una persona ha al massimo una card attiva', function () {
    $user = User::factory()->staff()->create();
    Card::factory()->for($user)->revoked()->create();
    Card::factory()->for($user)->create();

    expect(Card::where('user_id', $user->id)->count())->toBe(2);

    Card::factory()->for($user)->create();
})->throws(QueryException::class);

test('un conto ha esattamente un titolare: una persona o un\'auletta', function () {
    expect(Account::factory()->create()->isAulettaAccount())->toBeFalse()
        ->and(Account::factory()->forAuletta()->create()->isAulettaAccount())->toBeTrue();

    Account::factory()->create(['user_id' => null, 'auletta_id' => null]);
})->throws(QueryException::class);

test('una persona ha un solo conto per magazzino', function () {
    $account = Account::factory()->create();

    Account::factory()->create(['user_id' => $account->user_id, 'warehouse_id' => $account->warehouse_id]);
})->throws(QueryException::class);

test('i movimenti non si possono modificare', function () {
    $transaction = makeTransaction(Account::factory()->create());

    $transaction->update(['note' => 'modificato']);
})->throws(QueryException::class, 'append-only');

test('i movimenti non si possono cancellare', function () {
    makeTransaction(Account::factory()->create())->delete();
})->throws(QueryException::class, 'append-only');

test('il segno dell\'importo deve essere coerente con il tipo', function () {
    makeTransaction(Account::factory()->create(), ['type' => TransactionType::TopUp, 'amount_cents' => -100]);
})->throws(QueryException::class);

test('una rettifica richiede sempre una nota', function () {
    makeTransaction(Account::factory()->create(), ['type' => TransactionType::Adjustment, 'amount_cents' => -100]);
})->throws(QueryException::class);

test('lo stesso movimento ritrasmesso dal dispositivo non viene registrato due volte', function () {
    $account = Account::factory()->create();
    $device = Device::factory()->create();
    $id = (string) Str::uuid7(); // generato dall'ESP32

    makeTransaction($account, ['id' => $id, 'device_id' => $device->id]);
    makeTransaction($account, ['id' => $id, 'device_id' => $device->id]);
})->throws(QueryException::class);

test('un solo caffè di compleanno all\'anno per persona', function () {
    $account = Account::factory()->create();
    $gift = fn () => makeTransaction($account, ['type' => TransactionType::BirthdayGift, 'amount_cents' => 0, 'balance_after_cents' => 0]);

    BirthdayGift::create(['transaction_id' => $gift()->id, 'year' => 2026, 'user_id' => $account->user_id]);
    BirthdayGift::create(['transaction_id' => $gift()->id, 'year' => 2026, 'user_id' => $account->user_id]);
})->throws(QueryException::class);

test('un coupon si attiva una sola volta per persona', function () {
    $user = User::factory()->staff()->create();
    $coupon = Coupon::create([
        'code' => 'BENVENUTO',
        'discount_percent' => 20,
        'starts_at' => now(),
        'expires_at' => now()->addMonth(),
        'created_by' => User::factory()->admin()->create()->id,
    ]);

    $coupon->activations()->attach($user);
    $coupon->activations()->attach($user);
})->throws(QueryException::class);
