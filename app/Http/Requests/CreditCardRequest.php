<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreditCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'issuer' => ['required', 'string', 'max:120'],
            'last_four' => ['nullable', 'digits:4'],
            'credit_limit' => ['required', 'regex:/^[\d.,\s]+$/'],
            'closing_day' => ['required', 'integer', 'between:1,31'],
            'due_day' => ['required', 'integer', 'between:1,31'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
