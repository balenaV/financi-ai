<?php

namespace Tests\Feature;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportFormat;
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
        $this->get(route('reports.index'))->assertRedirect(route('login'));
    }

    public function test_user_cannot_view_or_delete_another_users_account(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $account = Account::factory()->for($owner)->create();

        $this->actingAs($attacker)->get(route('accounts.show', $account))->assertForbidden();
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

    public function test_user_cannot_contribute_to_another_users_goal(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $goal = FinancialGoal::factory()->for($owner)->create();

        $this->actingAs($attacker)->post(route('goals.contribute', $goal), ['amount' => '10,00'])->assertForbidden();
        $this->assertDatabaseCount('goal_contributions', 0);
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

    public function test_user_cannot_access_another_users_import_batch(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $account = Account::factory()->for($owner)->create();
        $batch = $owner->importBatches()->create([
            'account_id' => $account->id,
            'filename' => 'extrato.csv',
            'format' => ImportFormat::Csv,
            'status' => ImportBatchStatus::Parsed,
        ]);
        $row = $batch->rows()->create([
            'posted_at' => today(),
            'description_raw' => 'Mercado',
            'description' => 'Mercado',
            'type' => 'expense',
            'amount' => '10.00',
            'fingerprint' => 'x',
            'status' => 'new',
        ]);

        $this->actingAs($attacker)->getJson(route('transactions.import.show', $batch))->assertForbidden();
        $this->actingAs($attacker)->postJson(route('transactions.import.preview', $batch), [])->assertForbidden();
        $this->actingAs($attacker)->postJson(route('transactions.import.commit', $batch), ['row_ids' => [$row->id]])->assertForbidden();
        $this->actingAs($attacker)->post(route('transactions.import.revert', $batch))->assertForbidden();
    }
}
