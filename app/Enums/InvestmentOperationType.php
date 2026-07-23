<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum InvestmentOperationType: string
{
    use HasOptions;

    case Contribution = 'contribution';
    case Withdrawal = 'withdrawal';
    case Buy = 'buy';
    case Sell = 'sell';
    case Yield = 'yield';
    case Dividend = 'dividend';
    case ValueAdjustment = 'value_adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Contribution => 'Aporte',
            self::Withdrawal => 'Retirada',
            self::Buy => 'Compra',
            self::Sell => 'Venda',
            self::Yield => 'Rendimento',
            self::Dividend => 'Dividendo',
            self::ValueAdjustment => 'Ajuste de valor',
        };
    }
}
