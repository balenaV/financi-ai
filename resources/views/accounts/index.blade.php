<x-app-layout>
    <x-slot name="title">Contas</x-slot>
    <x-page-header title="Contas" description="Saldos calculados a partir das movimentações, sem divergências.">
        <a href="{{ route('accounts.create') }}" class="btn-primary">＋ Nova conta</a>
    </x-page-header>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($accounts as $row)
            @php($account = $row['account'])
            <article class="surface p-5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-2xl text-sm font-bold text-white" style="background: {{ $account->color }}">{{ mb_strtoupper(mb_substr($account->name, 0, 2)) }}</span><div><h2 class="font-bold">{{ $account->name }}</h2><p class="text-xs text-slate-500">{{ $account->institution ?: $account->type->label() }}</p></div></div>
                    <x-badge :tone="$account->active ? 'success' : 'neutral'">{{ $account->active ? 'Ativa' : 'Inativa' }}</x-badge>
                </div>
                <p class="mt-6 text-sm text-slate-500">Saldo atual</p><p class="mt-1 text-2xl font-bold text-primary-900"><x-money :value="$row['current']" /></p>
                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4"><span class="text-xs text-slate-500">Projetado: <strong class="text-slate-700"><x-money :value="$row['projected']" /></strong></span><a href="{{ route('accounts.show', $account) }}" class="text-sm font-semibold text-primary-600">Detalhes →</a></div>
            </article>
        @empty
            <div class="md:col-span-2 xl:col-span-3"><x-empty-state title="Nenhuma conta cadastrada" message="Comece adicionando sua conta principal, carteira ou poupança."><x-slot name="action"><a href="{{ route('accounts.create') }}" class="btn-primary">Adicionar conta</a></x-slot></x-empty-state></div>
        @endforelse
    </div>
    <div class="mt-6">{{ $accounts->links() }}</div>
</x-app-layout>
