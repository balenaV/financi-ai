<?php

namespace Tests\Feature;

use App\Enums\DebtInstallmentStatus;
use App\Enums\DebtStatus;
use App\Enums\GoalStatus;
use App\Enums\InvestmentStatus;
use App\Enums\InvestmentType;
use App\Models\Account;
use App\Models\User;
use App\Services\AccountBalanceService;
use App\Services\BudgetService;
use App\Services\DebtService;
use App\Services\InvestmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtInvestmentBudgetGoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_debt_payment_creates_expense_and_marks_debt_as_paid(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '1000.00']);
        $debt = app(DebtService::class)->create($user, [
            'name' => 'Acordo',
            'creditor' => 'Credor',
            'original_amount' => '100.00',
            'expected_total_amount' => '100.00',
            'started_at' => today()->toDateString(),
            'first_due_date' => today()->toDateString(),
            'installment_count' => 1,
            'status' => DebtStatus::Active->value,
        ]);

        $installment = $debt->installments->first();
        app(DebtService::class)->pay($user, $installment, $account);

        $this->assertSame(DebtInstallmentStatus::Paid, $installment->refresh()->status);
        $this->assertSame(DebtStatus::Paid, $debt->refresh()->status);
        $this->assertDatabaseHas('transactions', ['source_type' => 'debt_installment', 'amount' => '100.00']);
        $this->assertSame('900.00', app(AccountBalanceService::class)->current($account));
        $this->assertSame('0.00', app(DebtService::class)->summary($debt)['remaining']);
    }

    public function test_investment_return_and_contribution_do_not_duplicate_expense(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '2000.00']);
        $investment = $user->investments()->create([
            'name' => 'CDB',
            'type' => InvestmentType::FixedIncome->value,
            'institution' => 'Banco',
            'invested_amount' => '0.00',
            'current_amount' => '0.00',
            'last_updated_at' => today(),
            'status' => InvestmentStatus::Active->value,
        ]);

        $service = app(InvestmentService::class);
        $service->addOperation($user, $investment, [
            'type' => 'contribution',
            'amount' => '500.00',
            'operation_date' => today()->toDateString(),
            'account_id' => $account->id,
        ]);
        $investment->refresh()->update(['current_amount' => '550.00']);

        $this->assertSame('50.00', $service->metrics($investment->refresh())['profit']);
        $this->assertSame('10.00', $service->metrics($investment)['return_percentage']);
        $this->assertSame('1500.00', app(AccountBalanceService::class)->current($account));
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseMissing('transactions', ['type' => 'expense']);
    }

    public function test_monthly_budget_and_goal_workflows(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = $user->categories()->where('name', 'Alimentação')->first();
        $budget = $user->budgets()->create([
            'category_id' => $category->id,
            'month' => now()->month,
            'year' => now()->year,
            'limit_amount' => '1000.00',
        ]);
        $user->transactions()->create([
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'description' => 'Mercado',
            'amount' => '760.00',
            'competence_date' => today(),
            'paid_at' => today(),
            'status' => 'completed',
        ]);

        $metrics = app(BudgetService::class)->metrics($budget);
        $this->assertSame('760.00', $metrics['used']);
        $this->assertSame('76.00', $metrics['percentage']);
        $this->assertSame('attention', $metrics['level']);

        $this->actingAs($user)->post(route('goals.store'), [
            'name' => 'Viagem',
            'target_amount' => '5000,00',
            'current_amount' => '1250,00',
            'deadline' => today()->addYear()->toDateString(),
            'color' => '#1d9e75',
            'status' => GoalStatus::Active->value,
            'use_account_balance' => '0',
        ])->assertRedirect(route('dashboard').'#metas');

        $this->assertDatabaseHas('financial_goals', ['user_id' => $user->id, 'target_amount' => '5000.00']);
    }
}
