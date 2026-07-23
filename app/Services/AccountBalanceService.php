<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\User;
use App\Support\Money;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class AccountBalanceService
{
    public function current(Account $account, ?CarbonInterface $until = null): string
    {
        return $this->calculate($account, [TransactionStatus::Completed->value], $until);
    }

    public function projected(Account $account, ?CarbonInterface $until = null): string
    {
        return $this->calculate($account, [
            TransactionStatus::Completed->value,
            TransactionStatus::Planned->value,
            TransactionStatus::Overdue->value,
        ], $until);
    }

    public function calculate(Account $account, array $statuses, ?CarbonInterface $until = null): string
    {
        $balance = $until && $account->initial_balance_date->gt($until)
            ? '0.00'
            : (string) $account->initial_balance;

        $source = $account->transactions()
            ->whereIn('status', $statuses)
            ->when($until, fn (Builder $query) => $query->whereDate('competence_date', '<=', $until));

        $income = (string) (clone $source)->where('type', TransactionType::Income->value)->sum('amount');
        $expense = (string) (clone $source)->where('type', TransactionType::Expense->value)->sum('amount');
        $transferDebit = (string) (clone $source)
            ->where('type', TransactionType::Transfer->value)
            ->where(fn (Builder $query) => $query->whereNull('source_type')->orWhere('source_type', '!=', 'investment_credit'))
            ->sum('amount');
        $investmentCredit = (string) (clone $source)
            ->where('type', TransactionType::Transfer->value)
            ->where('source_type', 'investment_credit')
            ->sum('amount');

        $incoming = (string) $account->incomingTransfers()
            ->where('type', TransactionType::Transfer->value)
            ->whereIn('status', $statuses)
            ->when($until, fn (Builder $query) => $query->whereDate('competence_date', '<=', $until))
            ->sum('amount');

        return bcadd(
            bcsub(
                bcadd(bcadd($balance, $income, 2), bcadd($incoming, $investmentCredit, 2), 2),
                bcadd($expense, $transferDebit, 2),
                2,
            ),
            '0',
            2,
        );
    }

    public function totalsFor(User $user, ?CarbonInterface $until = null): array
    {
        $current = '0.00';
        $projected = '0.00';

        $user->accounts()->active()->get()->each(function (Account $account) use (&$current, &$projected, $until) {
            $current = bcadd($current, $this->current($account, $until), 2);
            $projected = bcadd($projected, $this->projected($account, $until), 2);
        });

        return ['current' => Money::normalize($current), 'projected' => Money::normalize($projected)];
    }
}
