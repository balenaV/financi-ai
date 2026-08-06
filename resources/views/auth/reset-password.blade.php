<x-guest-layout>
    <div>
        <p class="auth-eyebrow">Recuperar acesso</p>
        <h1 class="auth-title">Crie uma nova senha</h1>
        <p class="auth-description">Use ao menos oito caracteres e evite repetir senhas de outros serviços.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-form.input label="E-mail" name="email" type="email" :value="$request->email" autocomplete="username" required autofocus />
        <x-form.input label="Nova senha" name="password" type="password" autocomplete="new-password" required />
        <x-form.input label="Confirmar nova senha" name="password_confirmation" type="password" autocomplete="new-password" required />
        <x-button type="submit" class="w-full">Redefinir senha</x-button>
    </form>
</x-guest-layout>
