<?php

namespace Database\Factories;

use App\Enums\InvestmentOperationType;
use App\Models\Investment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestmentOperationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'investment_id' => Investment::factory(),
            'type' => InvestmentOperationType::Contribution->value,
            'amount' => '500.00',
            'operation_date' => today(),
        ];
    }
}
