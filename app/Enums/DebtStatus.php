<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum DebtStatus: string
{
    use HasOptions;

    case Active = 'active';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Renegotiated = 'renegotiated';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativa',
            self::Paid => 'Quitada',
            self::Overdue => 'Atrasada',
            self::Renegotiated => 'Renegociada',
            self::Cancelled => 'Cancelada',
        };
    }
}
