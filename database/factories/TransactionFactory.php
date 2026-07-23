<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_id' => Account::factory(),
            'type' => TransactionType::Expense->value,
            'description' => fake()->sentence(3),
            'amount' => fake()->randomElement(['25.90', '100.00', '350.45']),
            'competence_date' => today(),
            'due_date' => today(),
            'status' => TransactionStatus::Completed->value,
            'paid_at' => today(),
        ];
    }
}
