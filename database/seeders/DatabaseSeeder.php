<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\CreditCardBillStatus;
use App\Enums\DebtStatus;
use App\Enums\GoalStatus;
use App\Enums\InvestmentStatus;
use App\Enums\InvestmentType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\User;
use App\Services\DebtService;
use App\Services\DefaultCategoryService;
use App\Services\InvestmentService;
use App\Services\TransferService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Dados de demonstração ignorados fora do ambiente local.');

            return;
        }

        $user = User::query()->firstOrCreate(
            ['email' => 'demo@financi.ai.local'],
            ['name' => 'Usuário Demonstração', 'password' => Hash::make('password'), 'email_verified_at' => now()],
        );

        if (! $user->settings()->exists()) {
            $user->settings()->create();
        }

        app(DefaultCategoryService::class)->seed($user);

        $checking = $user->accounts()->firstOrCreate(['name' => 'Conta principal'], [
            'type' => AccountType::Checking->value,
            'institution' => 'Banco Exemplo',
            'initial_balance' => '2500.00',
            'initial_balance_date' => now()->startOfYear(),
            'color' => '#534ab7',
            'icon' => 'bank',
            'currency' => 'BRL',
            'active' => true,
        ]);
        $wallet = $user->accounts()->firstOrCreate(['name' => 'Carteira'], [
            'type' => AccountType::Cash->value,
            'initial_balance' => '150.00',
            'initial_balance_date' => now()->startOfYear(),
            'color' => '#1d9e75',
            'icon' => 'wallet',
            'currency' => 'BRL',
            'active' => true,
        ]);

        if (! $user->transactions()->exists()) {
            $user->transactions()->createMany([
                [
                    'account_id' => $checking->id,
                    'category_id' => $user->categories()->where('name', 'Salário')->where('type', 'income')->value('id'),
                    'type' => TransactionType::Income->value,
                    'description' => 'Salário mensal',
                    'amount' => '7200.00',
                    'competence_date' => now()->startOfMonth()->addDays(4),
                    'paid_at' => now()->startOfMonth()->addDays(4),
                    'status' => TransactionStatus::Completed->value,
                ],
                [
                    'account_id' => $checking->id,
                    'category_id' => $user->categories()->where('name', 'Moradia')->value('id'),
                    'type' => TransactionType::Expense->value,
                    'description' => 'Aluguel',
                    'amount' => '1850.00',
                    'competence_date' => now()->startOfMonth()->addDays(7),
                    'paid_at' => now()->startOfMonth()->addDays(7),
                    'status' => TransactionStatus::Completed->value,
                ],
                [
                    'account_id' => $checking->id,
                    'category_id' => $user->categories()->where('name', 'Alimentação')->value('id'),
                    'type' => TransactionType::Expense->value,
                    'description' => 'Supermercado',
                    'amount' => '486.30',
                    'competence_date' => today()->subDays(2),
                    'paid_at' => today()->subDays(2),
                    'status' => TransactionStatus::Completed->value,
                ],
            ]);

            app(TransferService::class)->create($user, [
                'account_id' => $checking->id,
                'destination_account_id' => $wallet->id,
                'type' => TransactionType::Transfer->value,
                'description' => 'Dinheiro para despesas semanais',
                'amount' => '200.00',
                'competence_date' => today(),
                'paid_at' => today(),
                'status' => TransactionStatus::Completed->value,
            ]);
        }

        if (! $user->debts()->exists()) {
            app(DebtService::class)->create($user, [
                'name' => 'Empréstimo pessoal',
                'creditor' => 'Financeira Exemplo',
                'kind' => 'loan',
                'description' => 'Demonstração do acompanhamento de dívida.',
                'original_amount' => '3000.00',
                'expected_total_amount' => '3300.00',
                'interest_rate' => '1.2000',
                'started_at' => today()->subMonth()->toDateString(),
                'first_due_date' => today()->addWeek()->toDateString(),
                'installment_count' => 6,
                'status' => DebtStatus::Active->value,
            ]);
        }

        $creditCard = $user->creditCards()->firstOrCreate(['name' => 'Cartão principal'], [
            'issuer' => 'Banco Exemplo',
            'last_four' => '4821',
            'credit_limit' => '6000.00',
            'closing_day' => 2,
            'due_day' => 10,
            'color' => '#534ab7',
            'active' => true,
        ]);
        $user->creditCardBills()->firstOrCreate([
            'credit_card_id' => $creditCard->id,
            'reference_month' => now()->startOfMonth(),
        ], [
            'total_amount' => '1247.80',
            'due_date' => now()->day(min(now()->daysInMonth, $creditCard->due_day)),
            'status' => now()->day(min(now()->daysInMonth, $creditCard->due_day))->lt(today())
                ? CreditCardBillStatus::Overdue->value
                : CreditCardBillStatus::Pending->value,
            'notes' => 'Fatura demonstrativa com compras parceladas.',
        ]);

        if (! $user->investments()->exists()) {
            $investment = $user->investments()->create([
                'name' => 'Tesouro Selic 2029',
                'type' => InvestmentType::FixedIncome->value,
                'institution' => 'Corretora Exemplo',
                'ticker' => 'SELIC29',
                'invested_amount' => '0.00',
                'current_amount' => '0.00',
                'last_updated_at' => today(),
                'liquidity' => 'D+1',
                'status' => InvestmentStatus::Active->value,
            ]);
            app(InvestmentService::class)->addOperation($user, $investment, [
                'type' => 'contribution',
                'amount' => '2000.00',
                'operation_date' => today()->toDateString(),
                'account_id' => $checking->id,
            ]);
            $investment->update(['current_amount' => '2085.40']);
        }

        $food = $user->categories()->where('name', 'Alimentação')->where('type', 'expense')->first();
        $user->budgets()->firstOrCreate(
            ['category_id' => $food->id, 'month' => now()->month, 'year' => now()->year],
            ['limit_amount' => '900.00', 'notes' => 'Orçamento demonstrativo.'],
        );
        $user->financialGoals()->firstOrCreate(['name' => 'Reserva de emergência'], [
            'account_id' => $checking->id,
            'description' => 'Seis meses de despesas essenciais.',
            'target_amount' => '20000.00',
            'current_amount' => '5000.00',
            'deadline' => today()->addYear(),
            'color' => '#1d9e75',
            'status' => GoalStatus::Active->value,
            'use_account_balance' => false,
        ]);

        $this->command?->info('Demo local: demo@financi.ai.local / password');
    }
}
