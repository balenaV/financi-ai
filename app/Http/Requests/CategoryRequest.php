<?php

namespace App\Http\Requests;

use App\Enums\CategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories')->where(fn ($query) => $query
                    ->where('user_id', $this->user()->id)
                    ->where('type', $this->input('type'))
                    ->whereNull('deleted_at'))->ignore($categoryId),
            ],
            'type' => ['required', Rule::enum(CategoryType::class)],
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
                Rule::notIn(array_filter([$categoryId])),
            ],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['required', 'string', 'max:32'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
