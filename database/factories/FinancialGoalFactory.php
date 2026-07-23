<?php

namespace Database\Factories;

use App\Enums\GoalStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinancialGoalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Reserva de emergência',
            'target_amount' => '15000.00',
            'current_amount' => '3000.00',
            'deadline' => today()->addYear(),
            'color' => '#1d9e75',
            'status' => GoalStatus::Active->value,
            'use_account_balance' => false,
        ];
    }
}
