<x-guest-layout>
    <div>
        <p class="auth-eyebrow">Verificar e-mail</p>
        <h1 class="auth-title">Confira sua caixa de entrada</h1>
        <p class="auth-description">Enviamos um link para confirmar seu endereço. Se ele não chegou, você pode solicitar outro.</p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="mt-5 flex items-center gap-2.5 rounded-xl border border-success-200 bg-success-50 p-3.5 text-sm font-medium text-success-800" role="status">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <span>Um novo link de verificação foi enviado.</span>
        </div>
    @endif

    <div class="mt-6 space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button type="submit" class="w-full">Reenviar e-mail</x-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button type="submit" variant="ghost" class="w-full">Sair da conta</x-button>
        </form>
    </div>
</x-guest-layout>
