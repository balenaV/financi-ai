<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where(fn ($query) => $query
                    ->where('user_id', $this->user()->id)->whereNull('deleted_at')),
            ],
            // "mimes" sniffs content and has no reliable entry for the OFX
            // extension; "extensions" checks the reported extension instead —
            // the parser itself is what actually validates file content.
            'file' => ['required', 'file', 'max:10240', 'extensions:csv,txt,ofx'],
        ];
    }
}
