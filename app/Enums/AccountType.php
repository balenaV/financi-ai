<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum AccountType: string
{
    use HasOptions;

    case Checking = 'checking';
    case Savings = 'savings';
    case Cash = 'cash';
    case DigitalWallet = 'digital_wallet';
    case Investment = 'investment';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Checking => 'Conta corrente',
            self::Savings => 'Poupança',
            self::Cash => 'Dinheiro',
            self::DigitalWallet => 'Carteira digital',
            self::Investment => 'Conta de investimento',
            self::Other => 'Outros',
        };
    }
}
