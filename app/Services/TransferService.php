<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferService
{
    public function create(User $user, array $data): Transaction
    {
        $source = $user->accounts()->findOrFail($data['account_id']);
        $destination = $user->accounts()->findOrFail($data['destination_account_id']);

        if ($source->is($destination)) {
            throw ValidationException::withMessages([
                'destination_account_id' => 'A conta de destino deve ser diferente da conta de origem.',
            ]);
        }

        return DB::transaction(fn () => $user->transactions()->create([
            ...$data,
            'account_id' => $source->id,
            'destination_account_id' => $destination->id,
            'category_id' => null,
            'type' => TransactionType::Transfer->value,
            'amount' => Money::normalize($data['amount']),
        ]));
    }
}
