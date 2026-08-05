<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ImportBatchStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Parsing = 'parsing';
    case Parsed = 'parsed';
    case Committed = 'committed';
    case Failed = 'failed';
    case Reverted = 'reverted';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Aguardando',
            self::Parsing => 'Lendo arquivo',
            self::Parsed => 'Pronto para revisão',
            self::Committed => 'Importado',
            self::Failed => 'Falhou',
            self::Reverted => 'Desfeito',
        };
    }
}
