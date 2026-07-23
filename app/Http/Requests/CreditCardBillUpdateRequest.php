<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreditCardBillUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'total_amount' => ['required', 'regex:/^[\d.,\s]+$/', 'not_in:0,0.00,0,00'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
