<?php

namespace App\Services;

use App\Enums\InvestmentOperationType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Investment;
use App\Models\InvestmentOperation;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvestmentService
{
    public function addOperation(User $user, Investment $investment, array $data): InvestmentOperation
    {
        if ($investment->user_id !== $user->id) {
            throw ValidationException::withMessages(['investment_id' => 'Investimento inválido.']);
        }

        $account = isset($data['account_id'])
            ? $user->accounts()->findOrFail($data['account_id'])
            : null;

        return DB::transaction(function () use ($user, $investment, $data, $account) {
            $type = InvestmentOperationType::from($data['type']);
            $amount = Money::normalize($data['amount']);
            $operation = $user->investmentOperations()->create([
                ...$data,
                'investment_id' => $investment->id,
                'user_id' => $user->id,
                'amount' => $amount,
            ]);

            $invested = (string) $investment->invested_amount;
            $current = (string) $investment->current_amount;

            if (in_array($type, [InvestmentOperationType::Contribution, InvestmentOperationType::Buy], true)) {
                $invested = bcadd($invested, $amount, 2);
                $current = bcadd($current, $amount, 2);
                $this->createAccountMovement($user, $account, $operation, 'investment_debit');
            } elseif (in_array($type, [InvestmentOperationType::Withdrawal, InvestmentOperationType::Sell], true)) {
                $current = bccomp($current, $amount, 2) >= 0 ? bcsub($current, $amount, 2) : '0.00';
                $this->createAccountMovement($user, $account, $operation, 'investment_credit');
            } elseif ($type === InvestmentOperationType::ValueAdjustment) {
                $current = $amount;
            } elseif ($type === InvestmentOperationType::Dividend && $account) {
                $this->createAccountMovement($user, $account, $operation, 'investment_credit');
            } else {
                $current = bcadd($current, $amount, 2);
            }

            $investment->update([
                'invested_amount' => $invested,
                'current_amount' => $current,
                'last_updated_at' => $data['operation_date'],
            ]);

            return $operation->refresh();
        });
    }

    public function metrics(Investment $investment): array
    {
        $profit = bcsub((string) $investment->current_amount, (string) $investment->invested_amount, 2);

        return [
            'profit' => $profit,
            'return_percentage' => Money::percentage($profit, (string) $investment->invested_amount),
        ];
    }

    private function createAccountMovement(
        User $user,
        ?Account $account,
        InvestmentOperation $operation,
        string $direction,
    ): void {
        if (! $account) {
            return;
        }

        $transaction = $user->transactions()->create([
            'account_id' => $account->id,
            'type' => TransactionType::Transfer->value,
            'description' => "{$operation->type->label()}: {$operation->investment->name}",
            'amount' => $operation->amount,
            'competence_date' => $operation->operation_date,
            'paid_at' => $operation->operation_date,
            'status' => TransactionStatus::Completed->value,
            'source_type' => $direction,
            'source_id' => $operation->id,
        ]);

        $operation->update(['transaction_id' => $transaction->id]);
    }
}
