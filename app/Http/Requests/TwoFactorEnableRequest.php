<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * O botão "Ativar" só existe na UI quando o MFA está desligado — mas o
 * endpoint em si não tinha nenhuma defesa própria contra ser chamado direto
 * (ex.: sessão sequestrada) enquanto o MFA já estava ativo. Sem isso, um
 * POST bastava para gerar um secret novo e apagar os códigos de recuperação
 * do dono legítimo, sem nunca provar a senha — o mesmo efeito de desativar
 * o MFA, só que sem passar pela reautenticação que destroy() exige.
 */
class TwoFactorEnableRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if (! $this->user()->hasTwoFactorEnabled()) {
            return [];
        }

        return [
            'current_password' => ['required', 'current_password'],
        ];
    }
}
