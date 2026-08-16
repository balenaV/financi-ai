<?php

namespace App\Http\Requests;

use App\Enums\DashboardSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sections' => ['required', 'array', 'size:5'],
            'sections.*' => ['distinct', Rule::enum(DashboardSection::class)],
        ];
    }
}
