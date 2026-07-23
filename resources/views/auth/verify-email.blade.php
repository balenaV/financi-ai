<x-guest-layout>
    <div class="mb-6">
        <p class="eyebrow">Verificar e-mail</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-950">Confira sua caixa de entrada</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Enviamos um link para confirmar seu endereço. Se ele não chegou, você pode solicitar outro.</p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-5 rounded-xl border border-success-200 bg-success-50 p-3 text-sm font-medium text-success-800">
            Um novo link de verificação foi enviado.
        </div>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full justify-center">Reenviar e-mail</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-primary-500">
                Sair da conta
            </button>
        </form>
    </div>
</x-guest-layout>
