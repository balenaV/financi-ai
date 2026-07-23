<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\CreditCard;
use App\Models\CreditCardBill;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService
{
    public function __construct(
        private readonly InstallmentService $installments,
        private readonly TransferService $transfers,
        private readonly CreditCardService $creditCards,
    ) {}

    public function create(User $user, array $data): Collection
    {
        if (($data['payment_channel'] ?? 'account') === 'credit_card') {
            return $this->createCreditCardPurchases($user, $data);
        }

        if ($data['type'] === TransactionType::Transfer->value) {
            return collect([$this->transfers->create($user, $data)]);
        }

        return DB::transaction(function () use ($user, $data) {
            $created = collect();
            $base = $this->basePayload($data);

            if (($data['payment_mode'] ?? 'single') === 'installment') {
                foreach ($this->installments->schedule(
                    $data['amount'],
                    (int) $data['installment_count'],
                    $data['first_installment_date'],
                ) as $installment) {
                    $created->push($user->transactions()->create([...$base, ...$installment]));
                }

                return $created;
            }

            if (($data['recurrence_count'] ?? 1) > 1) {
                $group = (string) Str::uuid();
                $start = Carbon::parse($data['recurrence_start_date'] ?? $data['competence_date']);

                for ($i = 0; $i < (int) $data['recurrence_count']; $i++) {
                    $date = $start->copy()->addMonthsNoOverflow($i)->toDateString();
                    $created->push($user->transactions()->create([
                        ...$base,
                        'competence_date' => $date,
                        'due_date' => $date,
                        'recurrence_group_id' => $group,
                    ]));
                }

                return $created;
            }

            $created->push($user->transactions()->create($base));

            return $created;
        });
    }

    public function update(Transaction $transaction, array $data, bool $future = false): Transaction
    {
        return DB::transaction(function () use ($transaction, $data, $future) {
            $wasCardPurchase = $transaction->payment_channel === 'credit_card';
            $isCardPurchase = ($data['payment_channel'] ?? 'account') === 'credit_card';

            if ($wasCardPurchase && $transaction->creditCardBill) {
                $this->creditCards->adjustBillTotal(
                    $transaction->creditCardBill,
                    bcmul($transaction->amount, '-1', 2),
                );
            }

            if ($isCardPurchase) {
                $card = $transaction->user->creditCards()->findOrFail($data['credit_card_id']);
                $bill = $this->creditCards->billForPurchase($transaction->user, $card, $data['competence_date']);
                $payload = $this->cardPayload($data, $card, $bill);
                $this->creditCards->adjustBillTotal($bill, Money::normalize($data['amount']));
            } else {
                $payload = $this->basePayload($data);
                if ($wasCardPurchase) {
                    $payload = [
                        ...$payload,
                        'credit_card_id' => null,
                        'credit_card_bill_id' => null,
                        'source_type' => null,
                        'source_id' => null,
                    ];
                }
            }

            $transaction->update($payload);

            if ($future) {
                $groupColumn = $transaction->installment_group_id ? 'installment_group_id' : 'recurrence_group_id';
                $groupId = $transaction->{$groupColumn};

                if ($groupId) {
                    Transaction::query()
                        ->where('user_id', $transaction->user_id)
                        ->where($groupColumn, $groupId)
                        ->where('competence_date', '>', $transaction->competence_date)
                        ->whereNot('status', TransactionStatus::Completed->value)
                        ->update(array_intersect_key($payload, array_flip([
                            'account_id', 'category_id', 'description', 'status', 'notes',
                        ])));
                }
            }

            return $transaction->refresh();
        });
    }

    public function cancel(Transaction $transaction, bool $future = false): void
    {
        DB::transaction(function () use ($transaction, $future) {
            if ($transaction->payment_channel === 'credit_card'
                && $transaction->status !== TransactionStatus::Cancelled
                && $transaction->creditCardBill) {
                $this->creditCards->adjustBillTotal(
                    $transaction->creditCardBill,
                    bcmul($transaction->amount, '-1', 2),
                );
            }

            $transaction->update(['status' => TransactionStatus::Cancelled->value]);

            if ($future) {
                foreach (['installment_group_id', 'recurrence_group_id'] as $column) {
                    if ($transaction->{$column}) {
                        Transaction::query()
                            ->where('user_id', $transaction->user_id)
                            ->where($column, $transaction->{$column})
                            ->where('competence_date', '>=', $transaction->competence_date)
                            ->whereNot('status', TransactionStatus::Completed->value)
                            ->update(['status' => TransactionStatus::Cancelled->value]);
                    }
                }
            }
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            if ($transaction->payment_channel === 'credit_card'
                && $transaction->status !== TransactionStatus::Cancelled
                && $transaction->creditCardBill) {
                $this->creditCards->adjustBillTotal(
                    $transaction->creditCardBill,
                    bcmul($transaction->amount, '-1', 2),
                );
            }

            $transaction->delete();
        });
    }

    private function basePayload(array $data): array
    {
        return [
            'account_id' => $data['account_id'],
            'destination_account_id' => $data['destination_account_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'type' => $data['type'],
            'payment_channel' => 'account',
            'description' => $data['description'],
            'amount' => Money::normalize($data['amount']),
            'competence_date' => $data['competence_date'],
            'due_date' => $data['due_date'] ?? null,
            'paid_at' => $data['paid_at'] ?? null,
            'status' => $data['status'] ?? TransactionStatus::Planned->value,
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function createCreditCardPurchases(User $user, array $data): Collection
    {
        return DB::transaction(function () use ($user, $data) {
            $card = $user->creditCards()->where('active', true)->findOrFail($data['credit_card_id']);
            $items = (($data['payment_mode'] ?? 'single') === 'installment')
                ? $this->installments->schedule(
                    $data['amount'],
                    (int) $data['installment_count'],
                    $data['first_installment_date'],
                )
                : [[
                    'amount' => Money::normalize($data['amount']),
                    'competence_date' => $data['competence_date'],
                ]];
            $created = collect();

            foreach ($items as $item) {
                $purchaseData = [...$data, ...$item];
                $bill = $this->creditCards->billForPurchase($user, $card, $purchaseData['competence_date']);
                $transaction = $user->transactions()->create([
                    ...$this->cardPayload($purchaseData, $card, $bill),
                    ...array_intersect_key($item, array_flip([
                        'installment_group_id',
                        'installment_number',
                        'installment_total',
                    ])),
                ]);
                $this->creditCards->adjustBillTotal($bill, $transaction->amount);
                $created->push($transaction);
            }

            return $created;
        });
    }

    private function cardPayload(array $data, CreditCard $card, CreditCardBill $bill): array
    {
        return [
            'account_id' => null,
            'destination_account_id' => null,
            'credit_card_id' => $card->id,
            'credit_card_bill_id' => $bill->id,
            'category_id' => $data['category_id'] ?? null,
            'type' => TransactionType::Expense->value,
            'payment_channel' => 'credit_card',
            'description' => $data['description'],
            'amount' => Money::normalize($data['amount']),
            'competence_date' => $data['competence_date'],
            'due_date' => $bill->due_date,
            'paid_at' => null,
            'status' => TransactionStatus::Completed->value,
            'notes' => $data['notes'] ?? null,
            'source_type' => 'credit_card_purchase',
            'source_id' => $bill->id,
        ];
    }
}
