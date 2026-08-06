<x-guest-layout>
    <div>
        <p class="auth-eyebrow">Recuperar acesso</p>
        <h1 class="auth-title">Esqueceu sua senha?</h1>
        <p class="auth-description">Informe seu e-mail e enviaremos um link seguro para você criar uma nova senha.</p>
    </div>

    <x-auth-session-status class="mt-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf
        <x-form.input label="E-mail" name="email" type="email" autocomplete="email" required autofocus />
        <x-button type="submit" class="w-full">Enviar link de recuperação</x-button>
    </form>

    <a href="{{ route('login') }}" class="mt-5 block text-center text-sm font-semibold text-primary-700 hover:text-primary-600 dark:text-primary-400">Voltar para o login</a>
</x-guest-layout>
