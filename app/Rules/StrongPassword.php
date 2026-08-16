<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Espelha no servidor os cinco critérios do medidor de força de senha do
 * front-end (handoff `js/auth.js`): mínimo 8 caracteres, maiúscula, minúscula,
 * número e um símbolo da lista permitida — sem nenhum caractere fora dela.
 */
class StrongPassword implements ValidationRule
{
    private const ALLOWED_SYMBOLS = '!@#$%&*?_\-.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;

        if (! preg_match('/^[A-Za-z0-9'.self::ALLOWED_SYMBOLS.']*$/', $value)) {
            $fail('A senha só pode conter letras, números e os símbolos ! @ # $ % & * ? _ - .');

            return;
        }

        if (mb_strlen($value) < 8) {
            $fail('A senha precisa ter pelo menos 8 caracteres.');

            return;
        }

        if (! preg_match('/[A-Z]/', $value)) {
            $fail('A senha precisa de uma letra maiúscula.');

            return;
        }

        if (! preg_match('/[a-z]/', $value)) {
            $fail('A senha precisa de uma letra minúscula.');

            return;
        }

        if (! preg_match('/[0-9]/', $value)) {
            $fail('A senha precisa de um número.');

            return;
        }

        if (! preg_match('/['.self::ALLOWED_SYMBOLS.']/', $value)) {
            $fail('A senha precisa de um símbolo: ! @ # $ % & * ? _ - .');
        }
    }
}
