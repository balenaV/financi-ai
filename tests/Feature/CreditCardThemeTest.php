<?php

namespace Tests\Feature;

use App\Enums\CreditCardBillStatus;
use App\Enums\DebtStatus;
use App\Models\Account;
use App\Models\CreditCard;
use App\Models\User;
use App\Services\CreditCardService;
use App\Services\DebtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreditCardThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_card_bills_and_loan_installments_form_consolidated_debt(): void
    {
        $user = User::factory()->create();
        $card = CreditCard::factory()->for($user)->create();
        $cards = app(CreditCardService::class);

        $cards->createBill($user, $card, [
            'reference_month' => now()->format('Y-m'),
            'total_amount' => '850,40',
            'due_date' => today()->addDays(10)->toDateString(),
        ]);
        app(DebtService::class)->create($user, [
            'name' => 'Empréstimo',
            'creditor' => 'Banco',
            'kind' => 'loan',
            'original_amount' => '1000.00',
            'expected_total_amount' => '1200.00',
            'started_at' => today()->toDateString(),
            'first_due_date' => today()->addMonth()->toDateString(),
            'installment_count' => 3,
            'status' => DebtStatus::Active->value,
        ]);

        $summary = $cards->debtSummary($user);

        $this->assertSame('1200.00', $summary['loans']);
        $this->assertSame('850.40', $summary['cards']);
        $this->assertSame('2050.40', $summary['total']);
    }

    public function test_paying_credit_card_bill_creates_one_expense_and_removes_it_from_open_debt(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $card = CreditCard::factory()->for($user)->create();
        $service = app(CreditCardService::class);
        $bill = $service->createBill($user, $card, [
            'reference_month' => now()->format('Y-m'),
            'total_amount' => '499.90',
            'due_date' => today()->addDays(5)->toDateString(),
        ]);

        $service->pay($user, $bill, $account);

        $this->assertSame(CreditCardBillStatus::Paid, $bill->refresh()->status);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'account_id' => $account->id,
            'source_type' => 'credit_card_bill',
            'source_id' => $bill->id,
            'amount' => '499.90',
            'type' => 'expense',
        ]);
        $this->assertSame('0.00', $service->debtSummary($user)['cards']);

        $this->expectException(ValidationException::class);
        $service->pay($user, $bill->refresh(), $account);
    }

    public function test_user_can_register_card_and_monthly_bill_but_cannot_access_another_users_card(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->post(route('credit-cards.store'), [
            'name' => 'Cartão principal',
            'issuer' => 'Banco Teste',
            'last_four' => '1234',
            'credit_limit' => '5.000,00',
            'closing_day' => 3,
            'due_day' => 10,
            'color' => '#534ab7',
            'active' => '1',
        ]);
        $card = $user->creditCards()->firstOrFail();
        $response->assertRedirect(route('credit-cards.show', $card));

        $this->actingAs($user)->post(route('credit-cards.bills.store', $card), [
            'reference_month' => now()->format('Y-m'),
            'total_amount' => '780,25',
            'due_date' => today()->addDays(8)->toDateString(),
        ])->assertRedirect();
        $this->assertDatabaseHas('credit_card_bills', [
            'user_id' => $user->id,
            'credit_card_id' => $card->id,
            'total_amount' => '780.25',
        ]);

        $this->actingAs($other)->get(route('credit-cards.show', $card))->assertForbidden();
    }

    public function test_theme_value_visibility_and_font_awesome_navigation_are_persistent(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patchJson(route('settings.toggle-theme'))
            ->assertOk()
            ->assertJson(['theme' => 'dark']);
        $this->assertSame('dark', $user->settings->refresh()->theme);

        $dashboard = $this->actingAs($user)->get(route('dashboard'));
        $dashboard->assertOk()
            ->assertSee('class="dark"', false)
            ->assertSee('fa-chart-pie', false)
            ->assertSee('fa-eye', false)
            ->assertDontSee('>01<', false);

        $this->actingAs($user)
            ->patchJson(route('settings.toggle-values'))
            ->assertOk()
            ->assertJson(['hidden' => true]);
        $this->actingAs($user)->get(route('dashboard'))->assertSee('fa-eye-slash', false);
    }
}
