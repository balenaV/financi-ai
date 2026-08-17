<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Debt;
use App\Models\DebtInstallment;
use App\Models\FinancialGoal;
use App\Models\Investment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPrompt6Test extends TestCase
{
    use RefreshDatabase;

    public function test_category_rejects_a_color_outside_the_allowed_palette(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Mascote',
            'type' => 'expense',
            'color' => '#D68A3C',
            'icon' => 'fa-solid fa-tag',
        ])->assertSessionHasErrors('color');
    }

    public function test_category_rejects_an_arbitrary_icon_class(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Ícone livre',
            'type' => 'expense',
            'color' => '#137A4A',
            'icon' => 'fa-solid fa-skull-crossbones',
        ])->assertSessionHasErrors('icon');
    }

    public function test_dashboard_budgets_tab_respects_month_navigation(): void
    {
        $user = User::factory()->create();
        $category = $user->categories()->where('type', 'expense')->first();
        $current = Budget::factory()->for($user)->for($category)->create([
            'month' => now()->month,
            'year' => now()->year,
            'limit_amount' => '900.00',
        ]);
        $previousMonth = now()->subMonth();
        $other = Budget::factory()->for($user)->for($category)->create([
            'month' => $previousMonth->month,
            'year' => $previousMonth->year,
            'limit_amount' => '450.00',
        ]);

        $currentResponse = $this->actingAs($user)->get('/dashboard');
        $currentResponse->assertSee('900,00');
        $currentResponse->assertDontSee('450,00');

        $pastResponse = $this->actingAs($user)->get('/dashboard?bud_month='.$previousMonth->month.'&bud_year='.$previousMonth->year);
        $pastResponse->assertSee('450,00');
        $pastResponse->assertDontSee('900,00');
    }

    public function test_debt_installment_baixar_route_registers_payment(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '1000.00']);
        $debt = Debt::factory()->for($user)->create(['installment_count' => 1]);
        $installment = DebtInstallment::factory()->for($user)->for($debt)->create(['amount' => '250.00']);

        $this->actingAs($user)->post(route('debts.installments.pay', $installment), [
            'account_id' => $account->id,
        ])->assertRedirect(route('dashboard').'#dividas');

        $this->assertDatabaseHas('debt_installments', ['id' => $installment->id, 'status' => 'paid']);
        $this->assertDatabaseHas('transactions', ['source_type' => 'debt_installment', 'amount' => '250.00']);
    }

    public function test_debt_panel_shows_a_create_account_prompt_instead_of_baixar_when_user_has_no_active_account(): void
    {
        $user = User::factory()->create();
        $debt = Debt::factory()->for($user)->create(['installment_count' => 1]);
        DebtInstallment::factory()->for($user)->for($debt)->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('>Baixar<', false);
        $response->assertSee('Cadastrar conta');
    }

    public function test_debt_installment_baixar_route_is_blocked_for_another_users_installment(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $attackerAccount = Account::factory()->for($attacker)->create();
        $debt = Debt::factory()->for($owner)->create(['installment_count' => 1]);
        $installment = DebtInstallment::factory()->for($owner)->for($debt)->create();

        $this->actingAs($attacker)->post(route('debts.installments.pay', $installment), [
            'account_id' => $attackerAccount->id,
        ])->assertForbidden();

        $this->assertDatabaseHas('debt_installments', ['id' => $installment->id, 'status' => 'pending']);
    }

    public function test_investment_operation_route_updates_metrics_without_duplicating_expense(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '2000.00']);
        $investment = Investment::factory()->for($user)->create([
            'invested_amount' => '1000.00',
            'current_amount' => '1000.00',
        ]);

        $this->actingAs($user)->post(route('investments.operations.store', $investment), [
            'type' => 'contribution',
            'amount' => '300.00',
            'operation_date' => today()->toDateString(),
            'account_id' => $account->id,
        ])->assertRedirect();

        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseMissing('transactions', ['type' => 'expense']);
        $this->assertDatabaseHas('investment_operations', [
            'investment_id' => $investment->id,
            'amount' => '300.00',
            'type' => 'contribution',
        ]);
    }

    public function test_goal_contribute_route_increases_current_amount(): void
    {
        $user = User::factory()->create();
        $goal = FinancialGoal::factory()->for($user)->create(['current_amount' => '500.00']);

        $this->actingAs($user)->post(route('goals.contribute', $goal), [
            'amount' => '150,00',
        ])->assertRedirect(route('dashboard').'#metas');

        $this->assertSame('650.00', $goal->refresh()->current_amount);
        $this->assertDatabaseHas('goal_contributions', [
            'user_id' => $user->id,
            'financial_goal_id' => $goal->id,
            'amount' => '150.00',
        ]);
    }

    public function test_goal_contribute_rejects_an_amount_long_enough_to_overflow_the_decimal_column(): void
    {
        $user = User::factory()->create();
        $goal = FinancialGoal::factory()->for($user)->create(['current_amount' => '500.00']);

        $this->actingAs($user)->post(route('goals.contribute', $goal), [
            'amount' => '999999999999999999,00',
        ])->assertSessionHasErrors('amount');

        $this->assertSame('500.00', $goal->refresh()->current_amount);
        $this->assertDatabaseCount('goal_contributions', 0);
    }

    public function test_goal_contributions_history_is_isolated_per_user(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $goal = FinancialGoal::factory()->for($owner)->create(['current_amount' => '0.00']);

        $this->actingAs($owner)->post(route('goals.contribute', $goal), ['amount' => '100,00'])->assertRedirect();

        $this->assertCount(1, $owner->goalContributions()->get());
        $this->assertCount(0, $attacker->goalContributions()->get());

        $response = $this->actingAs($attacker)->get('/dashboard');
        $response->assertOk();
        $response->assertDontSee($goal->name);
    }

    public function test_goal_contribute_route_is_blocked_for_another_users_goal(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $goal = FinancialGoal::factory()->for($owner)->create(['current_amount' => '500.00']);

        $this->actingAs($attacker)->post(route('goals.contribute', $goal), [
            'amount' => '150,00',
        ])->assertForbidden();

        $this->assertSame('500.00', $goal->refresh()->current_amount);
        $this->assertDatabaseCount('goal_contributions', 0);
    }

    public function test_dashboard_edit_query_params_never_resolve_another_users_resources(): void
    {
        $victim = User::factory()->create();
        $victimCategory = $victim->categories()->where('type', 'expense')->first();
        $victimBudget = Budget::factory()->for($victim)->for($victimCategory)->create();
        $victimDebt = Debt::factory()->for($victim)->create(['name' => 'Dívida secreta']);
        $victimInvestment = Investment::factory()->for($victim)->create(['name' => 'Investimento secreto']);
        $victimGoal = FinancialGoal::factory()->for($victim)->create(['name' => 'Meta secreta']);

        $attacker = User::factory()->create();

        $response = $this->actingAs($attacker)->get('/dashboard?'.http_build_query([
            'edit_category' => $victimCategory->id,
            'edit_budget' => $victimBudget->id,
            'edit_debt' => $victimDebt->id,
            'edit_investment' => $victimInvestment->id,
            'edit_goal' => $victimGoal->id,
        ]));

        $response->assertOk();
        $response->assertDontSee('Dívida secreta');
        $response->assertDontSee('Investimento secreto');
        $response->assertDontSee('Meta secreta');
    }

    /**
     * O fragmento de URL (#categorias, #dividas, #metas) nunca chega ao
     * servidor — back() sempre devolve /dashboard sem hash, o que reabre a
     * aba "Visão geral" e fecha qualquer painel que estivesse aberto. Todo
     * redirect de ação dentro de uma aba precisa apontar o hash explicitamente,
     * como já fazem os store/update dessas mesmas controllers.
     */
    public function test_category_and_debt_and_goal_actions_redirect_back_to_their_own_tab(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Categoria nova',
            'type' => 'expense',
            'color' => '#137A4A',
            'icon' => 'fa-solid fa-tag',
        ])->assertRedirect(route('dashboard').'#categorias');

        $category = Category::factory()->for($user)->create();
        $this->actingAs($user)->delete(route('categories.destroy', $category))
            ->assertRedirect(route('dashboard').'#categorias');

        $debt = Debt::factory()->for($user)->create(['installment_count' => 1]);
        $this->actingAs($user)->delete(route('debts.destroy', $debt))
            ->assertRedirect(route('dashboard').'#dividas');

        $goal = FinancialGoal::factory()->for($user)->create();
        $this->actingAs($user)->delete(route('goals.destroy', $goal))
            ->assertRedirect(route('dashboard').'#metas');
    }
}
