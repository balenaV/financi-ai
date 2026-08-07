<x-guest-layout>
    <div>
        <p class="auth-eyebrow">Área protegida</p>
        <h1 class="auth-title">Confirme sua senha</h1>
        <p class="auth-description">Por segurança, confirme sua senha antes de continuar.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
        @csrf
        <x-form.input label="Senha" name="password" type="password" autocomplete="current-password" required autofocus />
        <x-button type="submit" class="w-full">Confirmar</x-button>
    </form>
</x-guest-layout>
