<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommitImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'row_ids' => ['required', 'array', 'min:1'],
            'row_ids.*' => [
                'integer',
                Rule::exists('import_batch_rows', 'id')->where('import_batch_id', $this->route('batch')?->id),
            ],
            'categories' => ['nullable', 'array'],
            'categories.*' => [
                'nullable',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->where('user_id', $this->user()->id)->whereNull('deleted_at')),
            ],
        ];
    }
}
