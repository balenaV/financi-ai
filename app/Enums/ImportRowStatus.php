<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ImportRowStatus: string
{
    use HasOptions;

    case New = 'new';
    case DuplicateExact = 'duplicate_exact';
    case DuplicateProbable = 'duplicate_probable';
    case NeedsCategory = 'needs_category';
    case Invalid = 'invalid';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Novo',
            self::DuplicateExact => 'Já registrado',
            self::DuplicateProbable => 'Provável duplicata',
            self::NeedsCategory => 'Sem categoria',
            self::Invalid => 'Linha inválida',
        };
    }
}
