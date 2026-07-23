<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinancialReportService
{
    public function transactions(User $user, array $filters): Collection
    {
        $start = Carbon::parse($filters['start_date'] ?? now()->startOfMonth())->startOfDay();
        $end = Carbon::parse($filters['end_date'] ?? now()->endOfMonth())->endOfDay();

        return $user->transactions()
            ->with(['account', 'destinationAccount', 'category', 'creditCard'])
            ->whereNot('status', TransactionStatus::Cancelled->value)
            ->where(fn ($query) => $query->whereNull('source_type')->orWhere('source_type', '!=', 'credit_card_bill'))
            ->whereBetween('competence_date', [$start, $end])
            ->when($filters['account_id'] ?? null, fn ($query, $account) => $query->where(fn ($q) => $q->where('account_id', $account)->orWhere('destination_account_id', $account)))
            ->when(
                in_array($filters['type'] ?? null, array_column(TransactionType::cases(), 'value'), true)
                    ? $filters['type']
                    : null,
                fn ($query, $type) => $query->where('type', $type),
            )
            ->orderBy('competence_date')
            ->get();
    }

    public function summarize(Collection $transactions): array
    {
        $income = $transactions->where('type', TransactionType::Income)->reduce(
            fn ($total, $item) => bcadd($total, $item->amount, 2),
            '0.00',
        );
        $expense = $transactions->where('type', TransactionType::Expense)->reduce(
            fn ($total, $item) => bcadd($total, $item->amount, 2),
            '0.00',
        );

        return [
            'income' => $income,
            'expense' => $expense,
            'result' => bcsub($income, $expense, 2),
            'by_income_category' => $this->byCategory($transactions, TransactionType::Income),
            'by_expense_category' => $this->byCategory($transactions, TransactionType::Expense),
        ];
    }

    private function byCategory(Collection $transactions, TransactionType $type): Collection
    {
        return $transactions->where('type', $type)
            ->groupBy(fn ($item) => $item->category?->name ?? 'Sem categoria')
            ->map(fn ($items) => $items->reduce(fn ($total, $item) => bcadd($total, $item->amount, 2), '0.00'));
    }
}
