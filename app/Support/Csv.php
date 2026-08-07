<?php

namespace App\Support;

class Csv
{
    /**
     * Previne CSV formula injection: se o Excel/Sheets abrir o export e um
     * campo começar com =, +, - ou @, o programa interpreta como fórmula
     * em vez de texto. Prefixar com aspas simples neutraliza isso sem
     * alterar o valor visível para quem abre o arquivo.
     */
    public static function safe(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }
}
