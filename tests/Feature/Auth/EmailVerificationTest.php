<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_email_uses_the_financiai_brand(): void
    {
        $user = User::factory()->unverified()->create(['name' => 'Victor']);
        $message = (new VerifyEmail)->toMail($user);
        $html = (string) $message->render();

        $this->assertSame('Confirme seu e-mail — financi.ai', $message->subject);
        $this->assertSame('Olá, Victor!', $message->greeting);
        $this->assertSame('Confirmar meu e-mail', $message->actionText);
        $this->assertStringContainsString('clareza para suas finanças', $html);
        $this->assertStringContainsString('Organize hoje. Decida melhor amanhã.', $html);
        $this->assertStringContainsString('#22bf77', strtolower($html));
    }

    public function test_verification_notification_resend_is_throttled_per_email_address(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create(['email' => 'vitima@example.com']);

        $this->actingAs($user)->post('/email/verification-notification')->assertStatus(302);
        $this->actingAs($user)->post('/email/verification-notification')->assertStatus(302);

        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    public function test_verification_notification_can_be_resent_after_cooldown_expires(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create(['email' => 'vitima@example.com']);

        $this->actingAs($user)->post('/email/verification-notification');

        $this->travel(6)->minutes();

        $this->actingAs($user)->post('/email/verification-notification');

        Notification::assertSentToTimes($user, VerifyEmail::class, 2);
    }
}
