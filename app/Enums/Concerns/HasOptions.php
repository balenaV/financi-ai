<?php

namespace App\Enums\Concerns;

trait HasOptions
{
    public static function options(): array
    {
        return array_column(array_map(
            fn (self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases(),
        ), null);
    }
}
