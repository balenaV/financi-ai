<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForecastController extends Controller
{
    public function index(Request $request): View
    {
        $base = $request->user()->transactions()
            ->with(['account', 'category'])
            ->whereIn('status', [TransactionStatus::Planned, TransactionStatus::Overdue])
            ->whereDate('competence_date', '>=', today());

        $plannedIncome = (string) (clone $base)
            ->where('type', TransactionType::Income)
            ->sum('amount');
        $plannedExpense = (string) (clone $base)
            ->where('type', TransactionType::Expense)
            ->sum('amount');

        $monthly = (clone $base)
            ->whereDate('competence_date', '<=', today()->addMonths(12)->endOfMonth())
            ->orderBy('competence_date')
            ->get()
            ->groupBy(fn ($transaction) => $transaction->competence_date->format('Y-m'))
            ->map(function ($transactions, string $month) {
                $income = $transactions
                    ->where('type', TransactionType::Income)
                    ->reduce(fn ($total, $transaction) => bcadd($total, $transaction->amount, 2), '0.00');
                $expense = $transactions
                    ->where('type', TransactionType::Expense)
                    ->reduce(fn ($total, $transaction) => bcadd($total, $transaction->amount, 2), '0.00');

                return [
                    'month' => Carbon::createFromFormat('Y-m', $month)->startOfMonth(),
                    'income' => $income,
                    'expense' => $expense,
                    'result' => bcsub($income, $expense, 2),
                ];
            })->values();

        return view('forecast.index', [
            'transactions' => (clone $base)->orderBy('competence_date')->paginate(20),
            'monthly' => $monthly,
            'summary' => [
                'income' => Money::normalize($plannedIncome),
                'expense' => Money::normalize($plannedExpense),
                'result' => Money::normalize(bcsub($plannedIncome, $plannedExpense, 2)),
            ],
        ]);
    }
}
