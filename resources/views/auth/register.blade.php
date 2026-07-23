<x-guest-layout>
    <h1 class="text-2xl font-bold tracking-tight text-slate-950">Crie sua conta</h1><p class="mt-2 text-sm text-slate-500">Comece a organizar sua vida financeira.</p>
    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">@csrf
        <x-form.input label="Nome" name="name" autocomplete="name" required autofocus /><x-form.input label="E-mail" name="email" type="email" autocomplete="username" required /><x-form.input label="Senha" name="password" type="password" autocomplete="new-password" required /><x-form.input label="Confirmar senha" name="password_confirmation" type="password" autocomplete="new-password" required />
        <x-button type="submit" class="w-full">Criar conta</x-button>
    </form><p class="mt-6 text-center text-sm text-slate-500">Já tem cadastro? <a href="{{ route('login') }}" class="font-semibold text-primary-600">Entrar</a></p>
</x-guest-layout>
