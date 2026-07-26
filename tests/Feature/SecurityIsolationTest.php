<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\FinancialGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_financial_pages(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('accounts.index'))->assertRedirect(route('login'));
        $this->get(route('reports.index'))->assertRedirect(route('login'));
    }

    public function test_user_cannot_view_edit_or_delete_another_users_account(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $account = Account::factory()->for($owner)->create();

        $this->actingAs($attacker)->get(route('accounts.show', $account))->assertForbidden();
        $this->actingAs($attacker)->get(route('accounts.edit', $account))->assertForbidden();
        $this->actingAs($attacker)->delete(route('accounts.destroy', $account))->assertForbidden();
        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'deleted_at' => null]);
    }

    public function test_policy_blocks_foreign_goal_updates(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $goal = FinancialGoal::factory()->for($owner)->create();

        $this->assertFalse($attacker->can('update', $goal));
        $this->actingAs($attacker)->delete(route('goals.destroy', $goal))->assertForbidden();
    }

    public function test_foreign_category_cannot_be_attached_to_transaction(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = $other->categories()->where('type', 'expense')->first();

        $this->actingAs($user)->post(route('transactions.store'), [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'description' => 'Tentativa',
            'amount' => '20.00',
            'competence_date' => today()->toDateString(),
            'status' => 'completed',
            'payment_mode' => 'single',
            'recurrence_count' => 1,
        ])->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('transactions', ['user_id' => $user->id, 'description' => 'Tentativa']);
    }

    public function test_foreign_credit_card_cannot_be_used_in_transaction(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $category = Category::factory()->for($user)->create(['type' => 'expense']);
        $foreignCard = CreditCard::factory()->for($other)->create();

        $this->actingAs($user)->post(route('transactions.store'), [
            'payment_channel' => 'credit_card',
            'credit_card_id' => $foreignCard->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'description' => 'Compra indevida',
            'amount' => '99.90',
            'competence_date' => today()->toDateString(),
            'status' => 'completed',
            'payment_mode' => 'single',
            'recurrence_count' => 1,
        ])->assertSessionHasErrors('credit_card_id');

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $user->id,
            'description' => 'Compra indevida',
        ]);
    }
}
