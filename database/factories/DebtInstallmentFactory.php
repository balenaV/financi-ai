<?php

namespace Database\Factories;

use App\Enums\DebtInstallmentStatus;
use App\Models\Debt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DebtInstallmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'debt_id' => Debt::factory(),
            'number' => 1,
            'amount' => '275.00',
            'due_date' => today()->addMonth(),
            'status' => DebtInstallmentStatus::Pending->value,
        ];
    }
}
