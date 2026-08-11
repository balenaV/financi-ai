<?php

namespace Tests\Feature\Rules;

use App\Rules\TurnstileRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TurnstileRuleTest extends TestCase
{
    public function test_passes_and_logs_a_warning_when_secret_is_not_configured(): void
    {
        config(['services.turnstile.secret' => null]);
        Log::shouldReceive('warning')->once();

        $failed = false;
        (new TurnstileRule)->validate('cf-turnstile-response', null, function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
        Http::assertNothingSent();
    }

    public function test_fails_when_token_is_blank_and_secret_is_configured(): void
    {
        config(['services.turnstile.secret' => 'test-secret']);

        $failed = false;
        (new TurnstileRule)->validate('cf-turnstile-response', '', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
        Http::assertNothingSent();
    }

    public function test_passes_when_cloudflare_confirms_the_token(): void
    {
        config(['services.turnstile.secret' => 'test-secret']);
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true]),
        ]);

        $failed = false;
        (new TurnstileRule)->validate('cf-turnstile-response', 'valid-token', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
                && $request['secret'] === 'test-secret'
                && $request['response'] === 'valid-token';
        });
    }

    public function test_fails_when_cloudflare_rejects_the_token(): void
    {
        config(['services.turnstile.secret' => 'test-secret']);
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']]),
        ]);

        $failed = false;
        (new TurnstileRule)->validate('cf-turnstile-response', 'bad-token', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    public function test_fails_when_cloudflare_request_errors(): void
    {
        config(['services.turnstile.secret' => 'test-secret']);
        Http::fake(function () {
            throw new ConnectionException('timed out');
        });

        $failed = false;
        (new TurnstileRule)->validate('cf-turnstile-response', 'some-token', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    public function test_fails_when_cloudflare_returns_a_server_error(): void
    {
        config(['services.turnstile.secret' => 'test-secret']);
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response('', 500),
        ]);

        $failed = false;
        (new TurnstileRule)->validate('cf-turnstile-response', 'some-token', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }
}
