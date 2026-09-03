<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationService
{
    private const RECOVERY_CODES_COUNT = 8;

    public function __construct(private readonly Google2FA $google2fa) {}

    /**
     * Gera um novo secret e o mantém pendente no usuário (não confirmado)
     * até a etapa 2 do modal de ativação validar o primeiro código.
     */
    public function startActivation(User $user): string
    {
        $secret = $this->google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => null,
        ])->save();

        $user->twoFactorRecoveryCodes()->delete();

        return $secret;
    }

    public function qrCodeSvg(User $user, string $secret): string
    {
        $uri = $this->google2fa->getQRCodeUrl('financiaí', $user->email, $secret);

        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($uri);
    }

    public function verifyCode(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        return $this->google2fa->verifyKey($user->two_factor_secret, $code) === true;
    }

    /**
     * Confirma a ativação (etapa 2) e emite os códigos de recuperação em
     * claro — a única vez que eles existem fora da forma com hash.
     *
     * @return list<string>
     */
    public function confirm(User $user): array
    {
        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return $this->regenerateRecoveryCodes($user);
    }

    /**
     * @return list<string>
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $user->twoFactorRecoveryCodes()->delete();

        $codes = [];

        for ($i = 0; $i < self::RECOVERY_CODES_COUNT; $i++) {
            $code = Str::upper(Str::random(4).'-'.Str::random(4));
            $codes[] = $code;

            $user->twoFactorRecoveryCodes()->create(['code_hash' => Hash::make($code)]);
        }

        return $codes;
    }

    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $recoveryCode = $user->twoFactorRecoveryCodes()
            ->whereNull('used_at')
            ->get()
            ->first(fn ($stored) => Hash::check($code, $stored->code_hash));

        if (! $recoveryCode) {
            return false;
        }

        $recoveryCode->update(['used_at' => now()]);

        return true;
    }

    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $user->twoFactorRecoveryCodes()->delete();
    }
}
