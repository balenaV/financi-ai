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
        ])->assertRedirect(route('dashboard').'#contas');

        $this->assertDatabaseHas('accounts', ['user_id' => $user->id, 'name' => 'Banco principal', 'initial_balance' => '1500.25']);
    }

    public function test_history_lists_movements_newest_first_with_running_balance(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '100.00']);
        $other = Account::factory()->for($user)->create(['initial_balance' => '0.00']);

        $this->transaction($user, $account, TransactionType::Income, TransactionStatus::Completed, '50.00');
        $user->transactions()->create([
            'account_id' => $account->id,
            'destination_account_id' => $other->id,
            'type' => TransactionType::Transfer->value,
            'description' => 'Transferência para reserva',
            'amount' => '30.00',
            'competence_date' => today(),
            'status' => TransactionStatus::Completed->value,
        ]);

        $history = app(AccountBalanceService::class)->history($account);

        $this->assertCount(2, $history);
        $this->assertSame('-30.00', $history[0]['amount']);
        $this->assertSame('120.00', $history[0]['balance_after']);
        $this->assertSame('50.00', $history[1]['amount']);
        $this->assertSame('150.00', $history[1]['balance_after']);
    }

    public function test_user_can_archive_and_restore_an_account(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['active' => true]);

        $this->actingAs($user)->patch(route('accounts.archive', $account))
            ->assertRedirect(route('dashboard').'#contas');
        $this->assertFalse($account->fresh()->active);

        $this->actingAs($user)->patch(route('accounts.restore', $account))
            ->assertRedirect(route('dashboard').'#contas');
        $this->assertTrue($account->fresh()->active);
    }

    public function test_user_cannot_archive_another_users_account(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $account = Account::factory()->for($owner)->create(['active' => true]);

        $this->actingAs($attacker)->patch(route('accounts.archive', $account))->assertForbidden();
        $this->assertTrue($account->fresh()->active);
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
