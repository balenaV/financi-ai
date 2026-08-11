<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileRule implements ValidationRule
{
    /**
     * Implicit: precisa rodar mesmo se o campo vier ausente, senão bastaria
     * omitir o token para pular a verificação anti-robô.
     */
    public bool $implicit = true;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = config('services.turnstile.secret');

        if (blank($secret)) {
            Log::warning('Verificação do Cloudflare Turnstile desativada: TURNSTILE_SECRET_KEY não configurada.');

            return;
        }

        if (blank($value)) {
            $fail('Confirme que você não é um robô.');

            return;
        }

        try {
            $response = Http::asForm()->timeout(10)->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'secret' => $secret,
                    'response' => $value,
                ]
            );
        } catch (ConnectionException) {
            $fail('Não foi possível validar a verificação anti-robô no momento. Tente novamente.');

            return;
        }

        if (! $response->successful() || $response->json('success') !== true) {
            $fail('Não foi possível confirmar que você não é um robô.');
        }
    }
}
