<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_forms_validate_required_fields_and_positive_values(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        $this->actingAs($user)->post(route('accounts.store'), [])->assertSessionHasErrors(['name', 'type', 'initial_balance']);
        $this->actingAs($user)->post(route('transactions.store'), [
            'account_id' => $account->id,
            'type' => 'expense',
            'description' => '',
            'amount' => '0.00',
            'competence_date' => today()->toDateString(),
            'status' => 'planned',
        ])->assertSessionHasErrors(['description', 'amount', 'category_id']);
    }

    public function test_same_account_transfer_is_rejected_by_http_validation(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        $this->actingAs($user)->post(route('transactions.store'), [
            'account_id' => $account->id,
            'destination_account_id' => $account->id,
            'type' => 'transfer',
            'description' => 'Inválida',
            'amount' => '100.00',
            'competence_date' => today()->toDateString(),
            'status' => 'completed',
            'payment_mode' => 'single',
            'recurrence_count' => 1,
        ])->assertSessionHasErrors('destination_account_id');
    }
}
