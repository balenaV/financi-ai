<?php

namespace App\Enums;

enum CreditCardBillStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Em aberto',
            self::Paid => 'Paga',
            self::Overdue => 'Atrasada',
            self::Cancelled => 'Cancelada',
        };
    }
}
