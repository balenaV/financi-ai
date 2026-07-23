<?php

namespace App\Services;

use App\Enums\DebtInstallmentStatus;
use App\Enums\DebtStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtInstallment;
use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DebtService
{
    public function create(User $user, array $data): Debt
    {
        return DB::transaction(function () use ($user, $data) {
            $count = (int) $data['installment_count'];
            $firstDueDate = Carbon::parse($data['first_due_date'] ?? $data['due_date'] ?? $data['started_at']);
            unset($data['first_due_date']);
            $debt = $user->debts()->create([
                ...$data,
                'original_amount' => Money::normalize($data['original_amount']),
                'expected_total_amount' => Money::normalize($data['expected_total_amount']),
                'due_date' => $firstDueDate->copy()->addMonthsNoOverflow($count - 1)->toDateString(),
            ]);

            foreach (Money::split($data['expected_total_amount'], $count) as $index => $amount) {
                $user->debtInstallments()->create([
                    'debt_id' => $debt->id,
                    'user_id' => $user->id,
                    'number' => $index + 1,
                    'amount' => $amount,
                    'due_date' => $firstDueDate->copy()->addMonthsNoOverflow($index)->toDateString(),
                    'status' => DebtInstallmentStatus::Pending->value,
                ]);
            }

            return $debt->load('installments');
        });
    }

    public function pay(User $user, DebtInstallment $installment, Account $account, ?string $paidAt = null): DebtInstallment
    {
        if ($installment->user_id !== $user->id || $account->user_id !== $user->id) {
            throw ValidationException::withMessages(['account_id' => 'Conta ou parcela inválida.']);
        }

        return DB::transaction(function () use ($user, $installment, $account, $paidAt) {
            $locked = DebtInstallment::query()->lockForUpdate()->findOrFail($installment->id);

            if ($locked->status === DebtInstallmentStatus::Paid) {
                throw ValidationException::withMessages(['installment' => 'Esta parcela já foi paga.']);
            }

            $date = $paidAt ?: now()->toDateString();
            $category = $user->categories()->where('name', 'Dívidas')->first();
            $transaction = $user->transactions()->create([
                'account_id' => $account->id,
                'category_id' => $category?->id,
                'type' => TransactionType::Expense->value,
                'description' => "Pagamento {$locked->debt->name} — parcela {$locked->number}",
                'amount' => $locked->amount,
                'competence_date' => $date,
                'due_date' => $locked->due_date,
                'paid_at' => $date,
                'status' => TransactionStatus::Completed->value,
                'source_type' => 'debt_installment',
                'source_id' => $locked->id,
            ]);

            $locked->update([
                'transaction_id' => $transaction->id,
                'paid_at' => $date,
                'status' => DebtInstallmentStatus::Paid->value,
            ]);

            $debt = $locked->debt;
            if (! $debt->installments()->whereNotIn('status', [
                DebtInstallmentStatus::Paid->value,
                DebtInstallmentStatus::Cancelled->value,
            ])->exists()) {
                $debt->update(['status' => DebtStatus::Paid->value]);
            }

            return $locked->refresh();
        });
    }

    public function summary(Debt $debt): array
    {
        $paid = (string) $debt->installments()->where('status', DebtInstallmentStatus::Paid->value)->sum('amount');
        $total = (string) $debt->installments()->whereNot('status', DebtInstallmentStatus::Cancelled->value)->sum('amount');

        return [
            'total' => $total,
            'paid' => $paid,
            'remaining' => bcsub($total, $paid, 2),
            'percentage' => Money::percentage($paid, $total),
            'paid_count' => $debt->installments()->where('status', DebtInstallmentStatus::Paid->value)->count(),
            'overdue_count' => $debt->installments()->whereIn('status', [
                DebtInstallmentStatus::Pending->value,
                DebtInstallmentStatus::Overdue->value,
            ])->whereDate('due_date', '<', today())->count(),
            'next' => $debt->installments()->where('status', DebtInstallmentStatus::Pending->value)->orderBy('due_date')->first(),
        ];
    }
}
