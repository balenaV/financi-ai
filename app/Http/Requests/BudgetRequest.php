<?php

namespace App\Http\Requests;

use App\Enums\CategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $budgetId = $this->route('budget')?->id;

        return [
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->where('user_id', $this->user()->id)
                    ->where('type', CategoryType::Expense->value)
                    ->whereNull('deleted_at')),
            ],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'limit_amount' => ['required', 'regex:/^[\d.,\s]+$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $exists = $this->user()->budgets()
                    ->where('category_id', $this->input('category_id'))
                    ->where('month', $this->input('month'))
                    ->where('year', $this->input('year'))
                    ->when($this->route('budget'), fn ($query, $budget) => $query->whereKeyNot($budget->id))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('category_id', 'Já existe um orçamento para esta categoria no período.');
                }
            },
        ];
    }
}
