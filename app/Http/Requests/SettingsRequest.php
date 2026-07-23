<?php

namespace App\Http\Requests;

use DateTimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency' => ['required', Rule::in(['BRL', 'USD', 'EUR'])],
            'timezone' => ['required', Rule::in(DateTimeZone::listIdentifiers())],
            'financial_month_start_day' => ['required', 'integer', 'between:1,28'],
            'view_preference' => ['required', Rule::in(['comfortable', 'compact'])],
            'theme' => ['required', Rule::in(['light', 'dark'])],
            'hide_values' => ['sometimes', 'boolean'],
            'confirm_deletion' => ['sometimes', 'boolean'],
        ];
    }
}
