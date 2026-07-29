<x-guest-layout>
    <div class="auth-mode-switch" aria-label="Escolha uma forma de acesso">
        <a href="{{ route('login') }}" class="is-active" aria-current="page">Entrar</a>
        @if(config('features.registration'))
            <a href="{{ route('register') }}">Criar conta</a>
        @endif
    </div>

    <div class="mt-8">
        <p class="auth-eyebrow">Acesse sua conta</p>
        <h2 class="auth-title">Bem-vindo de volta.</h2>
        <p class="auth-description">Continue de onde parou e acompanhe sua vida financeira.</p>
    </div>

    <x-auth-session-status class="mt-5" :status="session('status')" />

    @if($errors->has('social'))
        <div class="auth-alert mt-5" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <span>{{ $errors->first('social') }}</span>
        </div>
    @endif

    <x-auth.social-options />

    <div class="auth-divider"><span>ou continue com e-mail</span></div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <x-form.input label="E-mail" name="email" type="email" autocomplete="username" required autofocus />
        <x-form.input label="Senha" name="password" type="password" autocomplete="current-password" required />

        <div class="flex items-center justify-between gap-3">
            <label class="flex cursor-pointer items-center gap-2 text-sm text-foreground-secondary">
                <input type="checkbox" name="remember" class="size-4 rounded">
                Lembrar de mim
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-primary-700 transition hover:text-primary-600 dark:text-primary-400">Esqueci a senha</a>
        </div>

        <x-button type="submit" class="w-full">Entrar com e-mail</x-button>
    </form>
</x-guest-layout>
