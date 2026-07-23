<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum CategoryType: string
{
    use HasOptions;

    case Income = 'income';
    case Expense = 'expense';

    public function label(): string
    {
        return $this === self::Income ? 'Receita' : 'Despesa';
    }
}
