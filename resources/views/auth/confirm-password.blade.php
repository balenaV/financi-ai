<x-guest-layout>
    <div class="mb-6">
        <p class="eyebrow">Área protegida</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-950">Confirme sua senha</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Por segurança, confirme sua senha antes de continuar.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" value="Senha" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">Confirmar</x-primary-button>
    </form>
</x-guest-layout>
