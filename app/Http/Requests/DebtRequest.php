<?php

namespace App\Http\Requests;

use App\Enums\DebtStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'creditor' => ['required', 'string', 'max:150'],
            'kind' => ['required', Rule::in(['loan', 'financing', 'agreement', 'other'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'original_amount' => ['required', 'regex:/^[\d.,\s]+$/'],
            'expected_total_amount' => ['required', 'regex:/^[\d.,\s]+$/'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'started_at' => ['required', 'date'],
            'first_due_date' => ['required_without:due_date', 'nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'installment_count' => ['required', 'integer', 'min:1', 'max:600'],
            'status' => ['required', Rule::enum(DebtStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
