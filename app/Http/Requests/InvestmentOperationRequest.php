<?php

namespace App\Http\Requests;

use App\Enums\InvestmentOperationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvestmentOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(InvestmentOperationType::class)],
            'amount' => ['required', 'regex:/^[\d.,\s]+$/'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'operation_date' => ['required', 'date'],
            'account_id' => [
                'nullable',
                Rule::exists('accounts', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)->whereNull('deleted_at')),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
