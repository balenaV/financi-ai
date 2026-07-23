<?php

namespace Database\Factories;

use App\Enums\CreditCardBillStatus;
use App\Models\CreditCard;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditCardBillFactory extends Factory
{
    public function definition(): array
    {
        return [
            'credit_card_id' => CreditCard::factory(),
            'user_id' => fn (array $attributes) => CreditCard::query()->findOrFail($attributes['credit_card_id'])->user_id,
            'reference_month' => now()->startOfMonth(),
            'total_amount' => '850.00',
            'due_date' => today()->addDays(10),
            'status' => CreditCardBillStatus::Pending->value,
        ];
    }
}
