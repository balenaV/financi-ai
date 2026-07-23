<?php

namespace App\Http\Requests;

use App\Enums\GoalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinancialGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'nullable',
                Rule::exists('accounts', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)->whereNull('deleted_at')),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'target_amount' => ['required', 'regex:/^[\d.,\s]+$/'],
            'current_amount' => ['required', 'regex:/^[\d.,\s]+$/'],
            'deadline' => ['nullable', 'date'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'status' => ['required', Rule::enum(GoalStatus::class)],
            'use_account_balance' => ['sometimes', 'boolean'],
        ];
    }
}
