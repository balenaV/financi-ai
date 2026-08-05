<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'column_map' => ['nullable', 'array'],
            'column_map.date' => ['required_with:column_map', 'string'],
            'column_map.description' => ['required_with:column_map', 'string'],
            'column_map.amount' => ['required_with:column_map', 'string'],
            'column_map.external_id' => ['nullable', 'string'],
            'date_format' => ['nullable', Rule::in(['DD/MM/AAAA', 'AAAA-MM-DD', 'MM/DD/AAAA'])],
            'decimal_separator' => ['nullable', Rule::in(['virgula', 'ponto'])],
        ];
    }
}
