<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Support\SecurityAudit;
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

        // GitHub só devolve e-mail primário e verificado (Socialite filtra isso
        // na própria chamada à API). O Google expõe "email_verified" no perfil e
        // pode retornar false — sem essa checagem, um e-mail não verificado no
        // Google bastaria pra vincular o login social a uma conta já existente
        // de outro usuário dono de verdade daquele e-mail (account takeover).
        $emailVerified = $providerUser->getRaw()['email_verified'] ?? true;
        if ($emailVerified === false || $emailVerified === 'false') {
            return redirect()->route('login')->withErrors([
                'social' => 'Seu e-mail ainda não foi verificado no provedor. Verifique-o e tente novamente.',
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

        // Um e-mail com conta local ainda não verificada pode ter sido registrado
        // por outra pessoa (occupation/pre-hijacking). Vincular e verificar à força
        // nesse momento daria a quem chegou primeiro com login social acesso a uma
        // conta que talvez não seja dele — então não logamos nem vinculamos aqui.
        if (! $user) {
            $existingUserByEmail = User::where('email', $email)->first();

            if ($existingUserByEmail && ! $existingUserByEmail->hasVerifiedEmail()) {
                return redirect()->route('login')->withErrors([
                    'social' => 'Já existe uma conta com este e-mail aguardando verificação. Verifique o e-mail dela ou entre com sua senha antes de usar o login social.',
                ]);
            }
        }

        $wasCreated = false;

        $user = DB::transaction(function () use ($user, $email, $provider, $providerUser, $providerUserId, &$wasCreated): User {
            $user ??= User::where('email', $email)->first();

            if (! $user) {
                $fallbackName = Str::headline(Str::before($email, '@'));
                $name = trim((string) ($providerUser->getName() ?: $providerUser->getNickname() ?: $fallbackName));

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(64)),
                ]);

                $wasCreated = true;
            }

            // Só marcamos como verificado o e-mail que o provedor acabou de confirmar.
            // Se a conta local (já vinculada por um login social anterior) teve o
            // e-mail trocado depois — ex.: em /profile — sem ainda provar posse do
            // endereço novo, esse endereço novo não deve ser verificado por tabela
            // (pre-hijacking via troca de e-mail pós-vínculo).
            if ($user->email === $email && ! $user->hasVerifiedEmail()) {
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

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->regenerate();
            $request->session()->put('two_factor.user_id', $user->id);
            $request->session()->put('two_factor.remember', true);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();
        SecurityAudit::log($user, 'acesso', 'Login com '.$this->providerLabel($provider), $request);

        if ($wasCreated) {
            $request->session()->flash('success', 'Bem-vindo(a) ao financiaí! Sua conta foi criada com sucesso.');
        }

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
