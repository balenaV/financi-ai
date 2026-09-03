<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_activate_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $secret = $this->app->make(TwoFactorAuthenticationService::class)->startActivation($user);
        $code = $this->app->make(Google2FA::class)->getCurrentOtp($secret);

        $response = $this->actingAs($user)->postJson(route('two-factor.confirm'), ['code' => $code]);

        $response->assertOk()->assertJsonStructure(['confirmed_at', 'recovery_codes']);
        $this->assertCount(8, $response->json('recovery_codes'));
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
        $this->assertSame(8, $user->fresh()->twoFactorRecoveryCodes()->count());
    }

    public function test_activation_rejects_invalid_code(): void
    {
        $user = User::factory()->create();
        $this->app->make(TwoFactorAuthenticationService::class)->startActivation($user);

        $response = $this->actingAs($user)->postJson(route('two-factor.confirm'), ['code' => '000000']);

        $response->assertStatus(422);
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_login_redirects_to_challenge_when_two_factor_is_enabled(): void
    {
        $user = $this->createUserWithTwoFactor();

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
        $this->assertSame($user->id, session('two_factor.user_id'));
    }

    public function test_challenge_completes_login_with_valid_totp_code(): void
    {
        $user = $this->createUserWithTwoFactor();
        $this->withSession(['two_factor.user_id' => $user->id]);

        $code = $this->app->make(Google2FA::class)->getCurrentOtp($user->two_factor_secret);

        $response = $this->post(route('two-factor.verify'), ['code' => $code]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_challenge_completes_login_with_valid_recovery_code(): void
    {
        $user = $this->createUserWithTwoFactor();
        $recoveryCode = $this->app->make(TwoFactorAuthenticationService::class)
            ->regenerateRecoveryCodes($user)[0];
        $this->withSession(['two_factor.user_id' => $user->id]);

        $response = $this->post(route('two-factor.verify'), ['code' => $recoveryCode, 'recovery' => true]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_recovery_code_cannot_be_reused(): void
    {
        $user = $this->createUserWithTwoFactor();
        $recoveryCode = $this->app->make(TwoFactorAuthenticationService::class)
            ->regenerateRecoveryCodes($user)[0];

        $this->withSession(['two_factor.user_id' => $user->id]);
        $this->post(route('two-factor.verify'), ['code' => $recoveryCode, 'recovery' => true]);
        $this->post('/logout');

        $this->withSession(['two_factor.user_id' => $user->id]);
        $response = $this->post(route('two-factor.verify'), ['code' => $recoveryCode, 'recovery' => true]);

        $response->assertSessionHasErrors('code', null, 'mfa');
        $this->assertGuest();
    }

    public function test_challenge_rejects_invalid_code(): void
    {
        $user = $this->createUserWithTwoFactor();
        $this->withSession(['two_factor.user_id' => $user->id]);

        $response = $this->post(route('two-factor.verify'), ['code' => '000000']);

        $response->assertSessionHasErrors('code', null, 'mfa');
        $this->assertGuest();
    }

    /**
     * Achado de segurança: sem essa proteção, uma sessão sequestrada bastava
     * para gerar um secret novo (sobrescrevendo o do dono legítimo) sem
     * nunca provar a senha — o mesmo efeito prático de desativar o MFA, só
     * que sem passar pela reautenticação que destroy() já exige.
     */
    public function test_reenabling_two_factor_while_already_active_requires_current_password(): void
    {
        $user = $this->createUserWithTwoFactor();
        $originalSecret = $user->two_factor_secret;

        $response = $this->actingAs($user)->postJson(route('two-factor.enable'));

        $response->assertStatus(422);
        $this->assertSame($originalSecret, $user->fresh()->two_factor_secret);
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_user_can_reenable_two_factor_with_correct_password(): void
    {
        $user = $this->createUserWithTwoFactor();
        $originalSecret = $user->two_factor_secret;

        $response = $this->actingAs($user)->postJson(route('two-factor.enable'), ['current_password' => 'password']);

        $response->assertOk()->assertJsonStructure(['key', 'qr']);
        $this->assertNotSame($originalSecret, $user->fresh()->two_factor_secret);
    }

    public function test_first_time_activation_does_not_require_a_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('two-factor.enable'))->assertOk();
    }

    public function test_confirming_two_factor_revokes_other_sessions_and_remember_token(): void
    {
        $user = User::factory()->create(['remember_token' => 'old-token']);
        DB::table('sessions')->insert([
            'id' => 'other-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => base64_encode('x'),
            'last_activity' => time(),
        ]);

        $secret = $this->app->make(TwoFactorAuthenticationService::class)->startActivation($user);
        $code = $this->app->make(Google2FA::class)->getCurrentOtp($secret);

        $this->actingAs($user)->postJson(route('two-factor.confirm'), ['code' => $code])->assertOk();

        $this->assertNotSame('old-token', $user->fresh()->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'other-session']);
    }

    public function test_disabling_two_factor_requires_current_password(): void
    {
        $user = $this->createUserWithTwoFactor();

        $response = $this->actingAs($user)->deleteJson(route('two-factor.disable'), ['current_password' => 'wrong']);

        $response->assertStatus(422);
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_user_can_disable_two_factor_with_correct_password(): void
    {
        $user = $this->createUserWithTwoFactor();

        $response = $this->actingAs($user)->deleteJson(route('two-factor.disable'), ['current_password' => 'password']);

        $response->assertOk();
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
        $this->assertSame(0, $user->fresh()->twoFactorRecoveryCodes()->count());
    }

    private function createUserWithTwoFactor(): User
    {
        $user = User::factory()->create();
        $service = $this->app->make(TwoFactorAuthenticationService::class);
        $secret = $service->startActivation($user);
        $service->confirm($user);

        return $user->fresh();
    }
}
