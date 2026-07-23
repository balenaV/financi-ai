<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditCardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Cartão '.fake()->word(),
            'issuer' => fake()->company(),
            'last_four' => fake()->numerify('####'),
            'credit_limit' => '5000.00',
            'closing_day' => 2,
            'due_day' => 10,
            'color' => '#534ab7',
            'active' => true,
        ];
    }
}
