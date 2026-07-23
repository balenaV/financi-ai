<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum GoalStatus: string
{
    use HasOptions;

    case Active = 'active';
    case Completed = 'completed';
    case Paused = 'paused';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativa',
            self::Completed => 'Concluída',
            self::Paused => 'Pausada',
            self::Cancelled => 'Cancelada',
        };
    }
}
