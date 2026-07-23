<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum TransactionStatus: string
{
    use HasOptions;

    case Planned = 'planned';
    case Completed = 'completed';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planejada',
            self::Completed => 'Efetivada',
            self::Overdue => 'Vencida',
            self::Cancelled => 'Cancelada',
        };
    }
}
