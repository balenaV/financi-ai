<x-guest-layout>
    <h1 class="text-2xl font-bold tracking-tight text-slate-950">Bem-vindo de volta</h1><p class="mt-2 text-sm text-slate-500">Entre para continuar cuidando das suas finanças.</p>
    <x-auth-session-status class="mt-4" :status="session('status')" />
    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">@csrf
        <x-form.input label="E-mail" name="email" type="email" autocomplete="username" required autofocus />
        <x-form.input label="Senha" name="password" type="password" autocomplete="current-password" required />
        <div class="flex items-center justify-between gap-3"><label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" class="size-4 rounded"> Lembrar de mim</label><a href="{{ route('password.request') }}" class="text-sm font-semibold text-primary-600">Esqueci a senha</a></div>
        <x-button type="submit" class="w-full">Entrar</x-button>
    </form>
    @if(config('features.registration'))<p class="mt-6 text-center text-sm text-slate-500">Ainda não tem conta? <a href="{{ route('register') }}" class="font-semibold text-primary-600">Cadastre-se</a></p>@endif
</x-guest-layout>
