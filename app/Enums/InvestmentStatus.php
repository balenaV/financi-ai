<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum InvestmentStatus: string
{
    use HasOptions;

    case Active = 'active';
    case Closed = 'closed';
    case Redeemed = 'redeemed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativo',
            self::Closed => 'Encerrado',
            self::Redeemed => 'Resgatado',
        };
    }
}
