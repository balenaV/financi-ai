<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Conta principal', 'Carteira', 'Poupança']),
            'type' => AccountType::Checking->value,
            'institution' => fake()->company(),
            'initial_balance' => '1000.00',
            'initial_balance_date' => today()->startOfYear(),
            'color' => '#534ab7',
            'icon' => 'wallet',
            'currency' => 'BRL',
            'active' => true,
        ];
    }
}
