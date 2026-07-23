<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransactionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_and_expense_can_be_created(): void
    {
        [$user, $account] = $this->userAndAccount();
        $income = $user->categories()->where('name', 'Salário')->first();
        $expense = $user->categories()->where('name', 'Alimentação')->first();

        foreach ([[TransactionType::Income, $income], [TransactionType::Expense, $expense]] as [$type, $category]) {
            $this->actingAs($user)->post(route('transactions.store'), $this->payload($account, $type, $category->id))
                ->assertRedirect(route('transactions.index'));
        }

        $this->assertDatabaseHas('transactions', ['user_id' => $user->id, 'type' => 'income']);
        $this->assertDatabaseHas('transactions', ['user_id' => $user->id, 'type' => 'expense']);
    }

    public function test_installments_and_recurrence_are_generated_in_advance(): void
    {
        [$user, $account] = $this->userAndAccount();
        $category = $user->categories()->where('name', 'Compras')->first();
        $service = app(TransactionService::class);

        $installments = $service->create($user, [
            ...$this->payload($account, TransactionType::Expense, $category->id),
            'amount' => '100.00',
            'payment_mode' => 'installment',
            'installment_count' => 3,
            'first_installment_date' => '2026-01-31',
        ]);
        $this->assertCount(3, $installments);
        $this->assertSame('100.00', $installments->reduce(fn ($total, $item) => bcadd($total, $item->amount, 2), '0.00'));
        $this->assertSame(['33.33', '33.33', '33.34'], $installments->pluck('amount')->all());
        $this->assertNotNull($installments->first()->installment_group_id);

        $recurring = $service->create($user, [
            ...$this->payload($account, TransactionType::Expense, $category->id),
            'payment_mode' => 'single',
            'recurrence_count' => 4,
            'recurrence_start_date' => '2026-02-01',
        ]);
        $this->assertCount(4, $recurring);
        $this->assertNotNull($recurring->first()->recurrence_group_id);
    }

    public function test_transfer_rejects_same_account(): void
    {
        [$user, $account] = $this->userAndAccount();

        $this->expectException(ValidationException::class);
        app(TransactionService::class)->create($user, [
            ...$this->payload($account, TransactionType::Transfer, null),
            'destination_account_id' => $account->id,
        ]);
    }

    public function test_transfer_rejects_another_users_account(): void
    {
        [$user, $account] = $this->userAndAccount();
        $other = User::factory()->create();
        $foreignAccount = Account::factory()->for($other)->create();

        $this->actingAs($user)->post(route('transactions.store'), [
            ...$this->payload($account, TransactionType::Transfer, null),
            'destination_account_id' => $foreignAccount->id,
        ])->assertSessionHasErrors('destination_account_id');
    }

    private function userAndAccount(): array
    {
        $user = User::factory()->create();

        return [$user, Account::factory()->for($user)->create()];
    }

    private function payload(Account $account, TransactionType $type, ?int $categoryId): array
    {
        return [
            'account_id' => $account->id,
            'category_id' => $categoryId,
            'type' => $type->value,
            'description' => 'Movimentação de teste',
            'amount' => '100.00',
            'competence_date' => today()->toDateString(),
            'due_date' => today()->toDateString(),
            'status' => TransactionStatus::Completed->value,
            'payment_mode' => 'single',
            'recurrence_count' => 1,
        ];
    }
}
