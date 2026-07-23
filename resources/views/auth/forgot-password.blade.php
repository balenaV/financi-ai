<x-guest-layout>
    <div class="mb-6">
        <p class="eyebrow">Recuperar acesso</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-950">Esqueceu sua senha?</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Informe seu e-mail e enviaremos um link seguro para você criar uma nova senha.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">Enviar link de recuperação</x-primary-button>
    </form>

    <a href="{{ route('login') }}" class="mt-5 block text-center text-sm font-semibold text-primary-700 hover:text-primary-900">Voltar para o login</a>
</x-guest-layout>
