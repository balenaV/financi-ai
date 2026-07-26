<x-app-layout>
    <x-slot name="title">Notificações</x-slot>
    <x-page-header title="Notificações" description="Lembretes de faturas, parcelas e transações planejadas.">
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf @method('PATCH')
            <x-button type="submit" variant="secondary">Marcar todas como lidas</x-button>
        </form>
    </x-page-header>

    <div class="surface divide-y divide-slate-100 overflow-hidden">
        @forelse($notifications as $notification)
            <form method="POST" action="{{ route('notifications.read', $notification) }}" class="{{ $notification->read_at ? '' : 'bg-primary-50/60' }}">
                @csrf @method('PATCH')
                <button class="flex w-full items-start gap-4 px-5 py-4 text-left hover:bg-slate-50">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-primary-100 text-primary-700">
                        <i class="fa-solid fa-bell" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 flex-1">
                        <strong class="block">{{ $notification->data['title'] ?? 'Lembrete financeiro' }}</strong>
                        <span class="mt-1 block text-sm text-slate-600">{{ $notification->data['message'] ?? '' }}</span>
                        <small class="mt-1 block text-slate-400">{{ $notification->created_at->diffForHumans() }}</small>
                    </span>
                    @unless($notification->read_at)<span class="mt-2 size-2 rounded-full bg-primary-500" aria-label="Não lida"></span>@endunless
                </button>
            </form>
        @empty
            <x-empty-state title="Nenhuma notificação" description="Seus próximos vencimentos aparecerão aqui." />
        @endforelse
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
</x-app-layout>
