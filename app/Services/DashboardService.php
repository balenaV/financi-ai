<?php

namespace App\Services;

use App\Enums\DebtInstallmentStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class DashboardService
{
    public function __construct(
        private readonly AccountBalanceService $balances,
        private readonly BudgetService $budgets,
        private readonly CreditCardService $creditCards,
    ) {}

    public function build(User $user, array $filters = []): array
    {
        [$start, $end] = $this->period($filters);
        $accountId = $filters['account_id'] ?? null;
        $accountQuery = $user->accounts()->where('active', true)->when($accountId, fn ($q) => $q->whereKey($accountId));
        $accounts = $accountQuery->get();

        $balanceCurrent = '0.00';
        $balanceProjected = '0.00';
        foreach ($accounts as $account) {
            $balanceCurrent = bcadd($balanceCurrent, $this->balances->current($account), 2);
            $balanceProjected = bcadd($balanceProjected, $this->balances->projected($account), 2);
        }

        $periodTransactions = $user->transactions()
            ->when($accountId, fn ($q) => $q->where(fn ($inner) => $inner->where('account_id', $accountId)->orWhere('destination_account_id', $accountId)))
            ->whereBetween('competence_date', [$start, $end]);

        $income = (string) (clone $periodTransactions)
            ->where('type', TransactionType::Income->value)
            ->where('status', TransactionStatus::Completed->value)
            ->sum('amount');
        $expense = (string) (clone $periodTransactions)
            ->where('type', TransactionType::Expense->value)
            ->where('status', TransactionStatus::Completed->value)
            ->where(fn ($query) => $query->whereNull('source_type')->orWhere('source_type', '!=', 'credit_card_bill'))
            ->sum('amount');
        $result = bcsub($income, $expense, 2);
        $plannedIncome = Money::normalize((string) (clone $periodTransactions)
            ->where('type', TransactionType::Income->value)
            ->whereIn('status', [TransactionStatus::Planned->value, TransactionStatus::Overdue->value])
            ->sum('amount'));
        $plannedExpense = Money::normalize((string) (clone $periodTransactions)
            ->where('type', TransactionType::Expense->value)
            ->whereIn('status', [TransactionStatus::Planned->value, TransactionStatus::Overdue->value])
            ->where(fn ($query) => $query->whereNull('source_type')->orWhere('source_type', '!=', 'credit_card_bill'))
            ->sum('amount'));

        $debtSummary = $this->creditCards->debtSummary($user);
        $debtTotal = $debtSummary['total'];
        $invested = (string) $user->investments()->where('status', 'active')->sum('current_amount');

        $recent = $user->transactions()->with(['account', 'category', 'destinationAccount', 'creditCard'])->latest('competence_date')->limit(8)->get();
        $upcoming = $user->transactions()->with('account')
            ->whereIn('status', [TransactionStatus::Planned->value, TransactionStatus::Overdue->value])
            ->whereBetween('due_date', [today(), today()->addDays(15)])
            ->orderBy('due_date')->limit(6)->get();
        $overdue = $user->transactions()
            ->whereIn('status', [TransactionStatus::Planned->value, TransactionStatus::Overdue->value])
            ->whereDate('due_date', '<', today())->count();

        $goals = $user->financialGoals()->with('account')->where('status', 'active')->orderBy('deadline')->limit(4)->get();
        $budgetRows = $user->budgets()->with(['category', 'user'])
            ->where('month', $start->month)->where('year', $start->year)->get()
            ->map(fn ($budget) => ['budget' => $budget, 'metrics' => $this->budgets->metrics($budget)]);

        return [
            'period' => compact('start', 'end'),
            'summary' => [
                'balance_current' => $balanceCurrent,
                'balance_projected' => $balanceProjected,
                'income' => $income,
                'expense' => $expense,
                'result' => $result,
                'planned_income' => $plannedIncome,
                'planned_expense' => $plannedExpense,
                'forecast_income' => bcadd($income, $plannedIncome, 2),
                'forecast_expense' => bcadd($expense, $plannedExpense, 2),
                'forecast_result' => bcsub(bcadd($income, $plannedIncome, 2), bcadd($expense, $plannedExpense, 2), 2),
                'debt_total' => $debtTotal,
                'overdue_bill_count' => $debtSummary['overdue_bills'],
                'invested' => $invested,
                'net_worth' => bcsub(bcadd($balanceCurrent, $invested, 2), $debtTotal, 2),
                'upcoming_count' => $upcoming->count(),
                'overdue_count' => $overdue,
                'savings_rate' => Money::percentage($result, $income),
            ],
            'accounts' => $accounts->map(fn (Account $account) => [
                'account' => $account,
                'current' => $this->balances->current($account),
                'projected' => $this->balances->projected($account),
            ]),
            'recent' => $recent,
            'upcoming' => $upcoming,
            'goals' => $goals,
            'budgets' => $budgetRows,
            'charts' => $this->charts($user, $end, $start, $accountId),
        ];
    }

    private function period(array $filters): array
    {
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            return [Carbon::parse($filters['start_date'])->startOfDay(), Carbon::parse($filters['end_date'])->endOfDay()];
        }

        $month = (int) ($filters['month'] ?? now()->month);
        $year = (int) ($filters['year'] ?? now()->year);
        $start = Carbon::create($year, $month)->startOfMonth();

        return [$start, $start->copy()->endOfMonth()];
    }

    private function charts(User $user, CarbonInterface $end, CarbonInterface $periodStart, mixed $accountId): array
    {
        $months = collect(range(5, 0))->map(fn ($offset) => Carbon::parse($end)->subMonthsNoOverflow($offset)->startOfMonth());
        $income = [];
        $expense = [];
        $balances = [];
        $debtEvolution = [];

        foreach ($months as $month) {
            $query = $user->transactions()
                ->when($accountId, fn ($q) => $q->where('account_id', $accountId))
                ->where('status', TransactionStatus::Completed->value)
                ->whereBetween('competence_date', [$month, $month->copy()->endOfMonth()]);
            $income[] = (string) (clone $query)->where('type', TransactionType::Income->value)->sum('amount');
            $expense[] = (string) (clone $query)
                ->where('type', TransactionType::Expense->value)
                ->where(fn ($inner) => $inner->whereNull('source_type')->orWhere('source_type', '!=', 'credit_card_bill'))
                ->sum('amount');
            $monthBalance = '0.00';
            foreach ($user->accounts()->when($accountId, fn ($q) => $q->whereKey($accountId))->get() as $account) {
                $monthBalance = bcadd($monthBalance, $this->balances->current($account, $month->copy()->endOfMonth()), 2);
            }
            $balances[] = $monthBalance;
            $paidUntil = (string) $user->debtInstallments()->where('status', DebtInstallmentStatus::Paid->value)
                ->whereDate('paid_at', '<=', $month->copy()->endOfMonth())->sum('amount');
            $allDebt = (string) $user->debtInstallments()->whereDate('due_date', '<=', $month->copy()->endOfMonth())->sum('amount');
            $debtEvolution[] = bcsub($allDebt, $paidUntil, 2);
        }

        $categoryExpenses = $user->transactions()->with('category')
            ->where('type', TransactionType::Expense->value)
            ->where('status', TransactionStatus::Completed->value)
            ->where(fn ($query) => $query->whereNull('source_type')->orWhere('source_type', '!=', 'credit_card_bill'))
            ->whereBetween('competence_date', [$periodStart, Carbon::parse($periodStart)->endOfMonth()])
            ->get()->groupBy(fn ($transaction) => $transaction->category?->name ?? 'Sem categoria')
            ->map(fn ($items) => $items->reduce(fn ($total, $item) => bcadd($total, $item->amount, 2), '0.00'));

        $investments = $user->investments()->where('status', 'active')->get()->groupBy(fn ($item) => $item->type->label())
            ->map(fn ($items) => $items->reduce(fn ($total, $item) => bcadd($total, $item->current_amount, 2), '0.00'));

        return [
            'labels' => $months->map(fn ($month) => $month->translatedFormat('M/y'))->values(),
            'income' => $income,
            'expense' => $expense,
            'balances' => $balances,
            'debt' => $debtEvolution,
            'expense_categories' => ['labels' => $categoryExpenses->keys()->values(), 'values' => $categoryExpenses->values()],
            'investments' => ['labels' => $investments->keys()->values(), 'values' => $investments->values()],
        ];
    }
}
