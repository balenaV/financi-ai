<?php

namespace App\Http\Controllers;

use App\Http\Requests\TwoFactorConfirmRequest;
use App\Http\Requests\TwoFactorEnableRequest;
use App\Http\Requests\TwoFactorPasswordConfirmationRequest;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Etapa 1: gera um novo secret pendente e devolve o QR + a chave manual.
     * TwoFactorEnableRequest exige a senha atual quando o MFA já está ativo
     * — ver o comentário da classe para o porquê.
     */
    public function store(TwoFactorEnableRequest $request, TwoFactorAuthenticationService $service): JsonResponse
    {
        $secret = $service->startActivation($request->user());

        return response()->json([
            'key' => trim(chunk_split($secret, 4, ' ')),
            'qr' => $service->qrCodeSvg($request->user(), $secret),
        ]);
    }

    /**
     * Etapa 2 → 3: confirma o código de 6 dígitos, ativa o MFA e devolve os
     * códigos de recuperação em claro — só aparecem aqui, uma vez.
     */
    public function confirm(TwoFactorConfirmRequest $request, TwoFactorAuthenticationService $service): JsonResponse
    {
        $user = $request->user();

        if (! $service->verifyCode($user, $request->validated('code'))) {
            return response()->json([
                'message' => 'Código inválido ou já expirado.',
            ], 422);
        }

        $recoveryCodes = $service->confirm($user);

        // Ativar o MFA precisa valer para qualquer sessão/dispositivo já
        // conectado, não só a partir de agora — senão um "remember me" ou
        // uma sessão antiga (ex.: de um acesso indevido anterior) continua
        // entrando sem nunca passar pelo desafio. Mesma exclusão da sessão
        // atual usada em ProfileController::logoutOtherSessions.
        $user->forceFill(['remember_token' => Str::random(60)])->save();
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return response()->json([
            'confirmed_at' => $user->fresh()->two_factor_confirmed_at->translatedFormat('d \d\e F \d\e Y'),
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    public function regenerateRecoveryCodes(
        TwoFactorPasswordConfirmationRequest $request,
        TwoFactorAuthenticationService $service,
    ): JsonResponse {
        return response()->json([
            'recovery_codes' => $service->regenerateRecoveryCodes($request->user()),
        ]);
    }

    public function destroy(
        TwoFactorPasswordConfirmationRequest $request,
        TwoFactorAuthenticationService $service,
    ): JsonResponse {
        $service->disable($request->user());

        return response()->json(['message' => 'Autenticação de dois fatores desativada.']);
    }
}
