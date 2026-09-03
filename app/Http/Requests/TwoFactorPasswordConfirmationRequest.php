<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Reautenticação por senha exigida tanto para desativar o MFA quanto para
 * gerar novos códigos de recuperação — mesmo padrão de current_password já
 * usado em ProfileController::destroy e ProfileUpdateRequest.
 */
class TwoFactorPasswordConfirmationRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
        ];
    }
}
