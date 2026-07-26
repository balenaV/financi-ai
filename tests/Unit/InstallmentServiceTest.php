<?php

namespace Tests\Unit;

use App\Services\InstallmentService;
use PHPUnit\Framework\TestCase;

class InstallmentServiceTest extends TestCase
{
    public function test_schedule_preserves_cents_group_and_end_of_month_dates(): void
    {
        $schedule = (new InstallmentService)->schedule('100.00', 3, '2026-01-31');

        $this->assertSame(['33.33', '33.33', '33.34'], array_column($schedule, 'amount'));
        $this->assertSame(
            ['2026-01-31', '2026-02-28', '2026-03-31'],
            array_column($schedule, 'competence_date'),
        );
        $this->assertSame([1, 2, 3], array_column($schedule, 'installment_number'));
        $this->assertSame([3, 3, 3], array_column($schedule, 'installment_total'));
        $this->assertNotEmpty($schedule[0]['installment_group_id']);
        $this->assertCount(1, array_unique(array_column($schedule, 'installment_group_id')));
    }
}
