<?php

namespace App\Services;

use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InstallmentService
{
    public function schedule(string $total, int $count, string $firstDate): array
    {
        $group = (string) Str::uuid();
        $date = Carbon::parse($firstDate);
        $amounts = Money::split($total, $count);

        return array_map(
            fn (string $amount, int $index) => [
                'amount' => $amount,
                'competence_date' => $date->copy()->addMonthsNoOverflow($index)->toDateString(),
                'due_date' => $date->copy()->addMonthsNoOverflow($index)->toDateString(),
                'installment_group_id' => $group,
                'installment_number' => $index + 1,
                'installment_total' => $count,
            ],
            $amounts,
            array_keys($amounts),
        );
    }
}
