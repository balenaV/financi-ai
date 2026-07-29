<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class SocialAuthenticationController extends Controller
{
    private const PROVIDERS = ['google', 'github'];

    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureSupportedProvider($provider);

        if (! $this->providerIsConfigured($provider)) {
            return redirect()->route('login')->withErrors([
                'social' => "O acesso com {$this->providerLabel($provider)} ainda não foi configurado.",
            ]);
        }

        $driver = Socialite::driver($provider);

        if ($provider === 'github') {
            $driver->scopes(['user:email']);
        }

        return $driver->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $this->ensureSupportedProvider($provider);

        if (! $this->providerIsConfigured($provider)) {
            return redirect()->route('login')->withErrors([
                'social' => "O acesso com {$this->providerLabel($provider)} ainda não foi configurado.",
            ]);
        }

        try {
            $providerUser = Socialite::driver($provider)->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'social' => 'Não foi possível concluir a autenticação. Tente novamente.',
            ]);
        }

        $email = Str::lower(trim((string) $providerUser->getEmail()));
        $providerUserId = trim((string) $providerUser->getId());

        if ($email === '' || $providerUserId === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('login')->withErrors([
                'social' => 'O provedor não retornou um endereço de e-mail válido.',
            ]);
        }

        $socialAccount = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->with('user')
            ->first();

        $user = $socialAccount?->user;

        if (! $user && ! config('features.registration') && ! User::where('email', $email)->exists()) {
            return redirect()->route('login')->withErrors([
                'social' => 'A criação de novas contas está temporariamente desativada.',
            ]);
        }

        $user = DB::transaction(function () use ($user, $email, $provider, $providerUser, $providerUserId): User {
            $user ??= User::where('email', $email)->first();

            if (! $user) {
                $fallbackName = Str::headline(Str::before($email, '@'));
                $name = trim((string) ($providerUser->getName() ?: $providerUser->getNickname() ?: $fallbackName));

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(64)),
                ]);
            }

            if (! $user->hasVerifiedEmail()) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $user->socialAccounts()->updateOrCreate(
                ['provider' => $provider],
                [
                    'provider_user_id' => $providerUserId,
                    'avatar_url' => $providerUser->getAvatar(),
                ],
            );

            return $user;
        });

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function ensureSupportedProvider(string $provider): void
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
    }

    private function providerIsConfigured(string $provider): bool
    {
        return filled(config("services.{$provider}.client_id"))
            && filled(config("services.{$provider}.client_secret"))
            && filled(config("services.{$provider}.redirect"));
    }

    private function providerLabel(string $provider): string
    {
        return $provider === 'google' ? 'Google' : 'GitHub';
    }
}
