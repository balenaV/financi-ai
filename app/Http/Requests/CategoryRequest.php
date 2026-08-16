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
            'color' => ['required', Rule::in([
                '#137A4A', '#2E9E5B', '#38C172', '#1F6F8B', '#3C6E9F',
                '#6B4FA8', '#B03A6E', '#C0392B', '#D68910', '#5B5A54',
            ])],
            'icon' => ['required', Rule::in([
                'fa-solid fa-tag', 'fa-solid fa-cart-shopping', 'fa-solid fa-house', 'fa-solid fa-car',
                'fa-solid fa-utensils', 'fa-solid fa-heart-pulse', 'fa-solid fa-graduation-cap', 'fa-solid fa-plane',
                'fa-solid fa-film', 'fa-solid fa-shirt', 'fa-solid fa-paw', 'fa-solid fa-briefcase',
                'fa-solid fa-piggy-bank', 'fa-solid fa-bolt', 'fa-solid fa-wifi', 'fa-solid fa-dumbbell',
            ])],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
