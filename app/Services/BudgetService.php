<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    public function metrics(Budget $budget): array
    {
        $start = Carbon::create($budget->year, $budget->month)->startOfMonth();
        $used = Money::normalize((string) $budget->user->transactions()
            ->where('category_id', $budget->category_id)
            ->where('type', TransactionType::Expense->value)
            ->where('status', TransactionStatus::Completed->value)
            ->whereBetween('competence_date', [$start, $start->copy()->endOfMonth()])
            ->sum('amount'));
        $remaining = bcsub((string) $budget->limit_amount, $used, 2);
        $percentage = Money::percentage($used, (string) $budget->limit_amount);

        return [
            'used' => $used,
            'remaining' => $remaining,
            'percentage' => $percentage,
            'level' => match (true) {
                bccomp($percentage, '100', 2) >= 0 => 'danger',
                bccomp($percentage, '90', 2) >= 0 => 'warning',
                bccomp($percentage, '75', 2) >= 0 => 'attention',
                default => 'ok',
            },
        ];
    }

    public function copyPreviousMonth(User $user, int $month, int $year): Collection
    {
        $target = Carbon::create($year, $month)->startOfMonth();
        $previous = $target->copy()->subMonth();

        return DB::transaction(function () use ($user, $target, $previous) {
            return $user->budgets()
                ->where('month', $previous->month)
                ->where('year', $previous->year)
                ->get()
                ->map(fn (Budget $budget) => $user->budgets()->updateOrCreate(
                    [
                        'category_id' => $budget->category_id,
                        'month' => $target->month,
                        'year' => $target->year,
                    ],
                    ['limit_amount' => $budget->limit_amount, 'notes' => $budget->notes],
                ));
        });
    }
}
