<x-guest-layout>
    <div class="auth-mode-switch" aria-label="Escolha uma forma de acesso">
        <a href="{{ route('login') }}">Entrar</a>
        <a href="{{ route('register') }}" class="is-active" aria-current="page">Criar conta</a>
    </div>

    <div class="mt-8">
        <p class="auth-eyebrow">Comece agora</p>
        <h2 class="auth-title">Crie sua conta.</h2>
        <p class="auth-description">Organize o presente e planeje seus próximos passos em poucos minutos.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf
        <x-form.input label="Nome" name="name" autocomplete="name" required autofocus />
        <x-form.input label="E-mail" name="email" type="email" autocomplete="username" required />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.input label="Senha" name="password" type="password" autocomplete="new-password" required />
            <x-form.input label="Confirmar senha" name="password_confirmation" type="password" autocomplete="new-password" required />
        </div>
        <x-button type="submit" class="w-full">Criar conta com e-mail</x-button>
    </form>

    <p class="mt-5 text-center text-xs leading-relaxed text-foreground-tertiary">
        Ao continuar, você confirma que os dados informados são seus.
    </p>
</x-guest-layout>
