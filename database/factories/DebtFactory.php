<?php

namespace Database\Factories;

use App\Enums\DebtStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DebtFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Empréstimo '.fake()->word(),
            'creditor' => fake()->company(),
            'original_amount' => '3000.00',
            'expected_total_amount' => '3300.00',
            'started_at' => today()->subMonth(),
            'due_date' => today()->addMonths(10),
            'installment_count' => 12,
            'status' => DebtStatus::Active->value,
        ];
    }
}
