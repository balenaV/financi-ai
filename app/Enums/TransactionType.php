<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum TransactionType: string
{
    use HasOptions;

    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Receita',
            self::Expense => 'Despesa',
            self::Transfer => 'Transferência',
        };
    }
}
