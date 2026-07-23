<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\User;
use App\Services\AccountBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_and_projected_balances_follow_transaction_statuses(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '1000.00']);

        $this->transaction($user, $account, TransactionType::Income, TransactionStatus::Completed, '500.00');
        $this->transaction($user, $account, TransactionType::Expense, TransactionStatus::Completed, '125.50');
        $this->transaction($user, $account, TransactionType::Expense, TransactionStatus::Planned, '200.00');
        $this->transaction($user, $account, TransactionType::Expense, TransactionStatus::Cancelled, '999.00');

        $service = app(AccountBalanceService::class);
        $this->assertSame('1374.50', $service->current($account));
        $this->assertSame('1174.50', $service->projected($account));
    }

    public function test_transfers_move_balance_without_becoming_income_or_expense(): void
    {
        $user = User::factory()->create();
        $source = Account::factory()->for($user)->create(['initial_balance' => '1000.00']);
        $destination = Account::factory()->for($user)->create(['initial_balance' => '100.00']);
        $user->transactions()->create([
            'account_id' => $source->id,
            'destination_account_id' => $destination->id,
            'type' => TransactionType::Transfer->value,
            'description' => 'Transferência',
            'amount' => '250.00',
            'competence_date' => today(),
            'paid_at' => today(),
            'status' => TransactionStatus::Completed->value,
        ]);

        $service = app(AccountBalanceService::class);
        $this->assertSame('750.00', $service->current($source));
        $this->assertSame('350.00', $service->current($destination));
        $this->assertSame('0.00', bcadd((string) $user->transactions()->where('type', TransactionType::Income->value)->sum('amount'), '0', 2));
        $this->assertSame('0.00', bcadd((string) $user->transactions()->where('type', TransactionType::Expense->value)->sum('amount'), '0', 2));
    }

    public function test_authenticated_user_can_create_an_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('accounts.store'), [
            'name' => 'Banco principal',
            'type' => AccountType::Checking->value,
            'initial_balance' => '1.500,25',
            'initial_balance_date' => today()->toDateString(),
            'color' => '#534ab7',
            'icon' => 'bank',
            'currency' => 'BRL',
            'active' => '1',
        ])->assertRedirect(route('accounts.index'));

        $this->assertDatabaseHas('accounts', ['user_id' => $user->id, 'name' => 'Banco principal', 'initial_balance' => '1500.25']);
    }

    private function transaction(User $user, Account $account, TransactionType $type, TransactionStatus $status, string $amount): void
    {
        $user->transactions()->create([
            'account_id' => $account->id,
            'category_id' => null,
            'type' => $type->value,
            'description' => fake()->sentence(2),
            'amount' => $amount,
            'competence_date' => today(),
            'status' => $status->value,
        ]);
    }
}
