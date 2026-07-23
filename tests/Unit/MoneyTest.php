<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_normalizes_brazilian_currency_without_float_calculation(): void
    {
        $this->assertSame('1234.56', Money::normalize('R$ 1.234,56'));
        $this->assertSame('12.30', Money::normalize('12.3'));
    }

    public function test_installments_preserve_every_cent(): void
    {
        $parts = Money::split('100.00', 3);

        $this->assertSame(['33.33', '33.33', '33.34'], $parts);
        $this->assertSame('100.00', array_reduce($parts, fn ($total, $value) => bcadd($total, $value, 2), '0.00'));
    }

    public function test_small_amounts_are_distributed_exactly(): void
    {
        $this->assertSame(['0.00', '0.00', '0.01'], Money::split('0.01', 3));
    }
}
