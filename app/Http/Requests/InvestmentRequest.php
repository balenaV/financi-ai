<?php

namespace App\Http\Requests;

use App\Enums\InvestmentStatus;
use App\Enums\InvestmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvestmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::enum(InvestmentType::class)],
            'institution' => ['required', 'string', 'max:150'],
            'ticker' => ['nullable', 'string', 'max:30'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'invested_amount' => ['required', 'regex:/^[\d.,\s]+$/'],
            'current_amount' => ['required', 'regex:/^[\d.,\s]+$/'],
            'last_updated_at' => ['required', 'date'],
            'liquidity' => ['nullable', 'string', 'max:100'],
            'maturity_date' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(InvestmentStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
