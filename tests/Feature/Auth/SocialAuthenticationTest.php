<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_access_is_offered_only_on_the_login_screen(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(route('social.redirect', ['provider' => 'google']))
            ->assertSee(route('social.redirect', ['provider' => 'github']));

        $this->get('/register')
            ->assertOk()
            ->assertDontSee(route('social.redirect', ['provider' => 'google']))
            ->assertDontSee(route('social.redirect', ['provider' => 'github']));
    }

    public function test_unsupported_social_provider_returns_not_found(): void
    {
        $this->get('/auth/unknown/redirect')->assertNotFound();
    }

    public function test_unconfigured_provider_returns_a_friendly_error(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
            'services.google.redirect' => null,
        ]);

        $this->get('/auth/google/redirect')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('social');
    }

    public function test_google_callback_creates_a_verified_user_and_social_account(): void
    {
        $this->configureProvider('google');
        $this->fakeProvider('google', [
            'id' => 'google-user-123',
            'name' => 'Victor Balena',
            'email' => 'victor@example.com',
            'avatar' => 'https://example.com/avatar.png',
        ]);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'victor@example.com',
            'name' => 'Victor Balena',
        ]);
        $this->assertNotNull(User::where('email', 'victor@example.com')->firstOrFail()->email_verified_at);
        $this->assertDatabaseHas('social_accounts', [
            'provider' => 'google',
            'provider_user_id' => 'google-user-123',
        ]);
    }

    public function test_github_callback_links_an_existing_user_without_duplicating_it(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'victor@example.com']);

        $this->configureProvider('github');
        $this->fakeProvider('github', [
            'id' => 'github-user-456',
            'name' => 'Victor',
            'email' => 'VICTOR@example.com',
        ]);

        $this->get('/auth/github/callback')->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_user_id' => 'github-user-456',
        ]);
    }

    public function test_social_registration_respects_the_registration_feature_flag(): void
    {
        config(['features.registration' => false]);
        $this->configureProvider('google');
        $this->fakeProvider('google', [
            'id' => 'new-google-user',
            'name' => 'Novo usuário',
            'email' => 'novo@example.com',
        ]);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('social');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    private function configureProvider(string $provider): void
    {
        config([
            "services.{$provider}.client_id" => 'client-id',
            "services.{$provider}.client_secret" => 'client-secret',
            "services.{$provider}.redirect" => "http://localhost/auth/{$provider}/callback",
        ]);
    }

    /**
     * @param  array{id: string, name?: string, email: string, avatar?: string}  $attributes
     */
    private function fakeProvider(string $provider, array $attributes): void
    {
        $socialiteUser = (new SocialiteUser)->map([
            'id' => $attributes['id'],
            'name' => $attributes['name'] ?? null,
            'email' => $attributes['email'],
            'avatar' => $attributes['avatar'] ?? null,
        ]);

        $driver = Mockery::mock(Provider::class);
        $driver->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->once()->with($provider)->andReturn($driver);
    }
}
