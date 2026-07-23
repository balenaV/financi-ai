<?php

namespace App\Support;

use NumberFormatter;

final class Money
{
    public static function normalize(string|int $value): string
    {
        $raw = preg_replace('/[^\d,.\-]/u', '', (string) $value) ?? '0';

        if (str_contains($raw, ',') && str_contains($raw, '.')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        } elseif (str_contains($raw, ',')) {
            $raw = str_replace(',', '.', $raw);
        }

        if (substr_count($raw, '.') > 1) {
            $parts = explode('.', $raw);
            $decimal = array_pop($parts);
            $raw = implode('', $parts).'.'.$decimal;
        }

        return bcadd($raw === '' ? '0' : $raw, '0', 2);
    }

    public static function toMinor(string|int $value): int
    {
        return (int) bcmul(self::normalize($value), '100', 0);
    }

    public static function fromMinor(int $value): string
    {
        $sign = $value < 0 ? '-' : '';
        $absolute = abs($value);

        return $sign.intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }

    /** @return list<string> */
    public static function split(string|int $total, int $count): array
    {
        if ($count < 1) {
            throw new \InvalidArgumentException('A quantidade de parcelas deve ser positiva.');
        }

        $minor = self::toMinor($total);
        $base = intdiv($minor, $count);
        $amounts = array_fill(0, $count, self::fromMinor($base));
        $amounts[$count - 1] = self::fromMinor($base + ($minor - ($base * $count)));

        return $amounts;
    }

    public static function format(string|int|null $value, bool $hide = false, string $currency = 'BRL'): string
    {
        if ($hide) {
            return 'R$ ••••••';
        }

        $formatter = new NumberFormatter('pt_BR', NumberFormatter::CURRENCY);

        return $formatter->formatCurrency((float) ($value ?? 0), $currency) ?: 'R$ 0,00';
    }

    public static function percentage(string|int $part, string|int $total): string
    {
        if (bccomp((string) $total, '0', 4) === 0) {
            return '0.00';
        }

        return bcmul(bcdiv((string) $part, (string) $total, 6), '100', 2);
    }
}
