<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum InvestmentType: string
{
    use HasOptions;

    case FixedIncome = 'fixed_income';
    case Stock = 'stock';
    case RealEstateFund = 'real_estate_fund';
    case Etf = 'etf';
    case Crypto = 'crypto';
    case Pension = 'pension';
    case Savings = 'savings';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FixedIncome => 'Renda fixa',
            self::Stock => 'Ação',
            self::RealEstateFund => 'Fundo imobiliário',
            self::Etf => 'ETF',
            self::Crypto => 'Criptomoeda',
            self::Pension => 'Previdência',
            self::Savings => 'Poupança',
            self::Other => 'Outros',
        };
    }
}
