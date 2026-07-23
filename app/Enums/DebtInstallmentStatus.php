<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum DebtInstallmentStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Paid => 'Paga',
            self::Overdue => 'Atrasada',
            self::Cancelled => 'Cancelada',
        };
    }
}
