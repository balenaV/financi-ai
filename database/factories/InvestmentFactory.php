<?php

namespace Database\Factories;

use App\Enums\InvestmentStatus;
use App\Enums\InvestmentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Tesouro Selic',
            'type' => InvestmentType::FixedIncome->value,
            'institution' => fake()->company(),
            'invested_amount' => '5000.00',
            'current_amount' => '5250.00',
            'last_updated_at' => today(),
            'liquidity' => 'D+1',
            'status' => InvestmentStatus::Active->value,
        ];
    }
}
