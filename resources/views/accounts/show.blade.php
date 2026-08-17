<x-app-layout>
    <x-slot name="title">{{ $account->name }}</x-slot>
    <x-page-header :title="$account->name" :description="$account->institution ?: $account->type->label()">
        <a href="{{ route('dashboard', ['edit_account' => $account->id]) }}#contas" class="btn-secondary">Editar conta</a>
        <a href="{{ route('dashboard') }}" class="btn-primary">Nova transação</a>
    </x-page-header>
    <div class="grid gap-4 sm:grid-cols-2"><x-financial-card label="Saldo atual" :value="$current" tone="primary" /><x-financial-card label="Saldo projetado" :value="$projected" /></div>
    <div class="surface mt-6 overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-bold">Últimas movimentações</h2></div>
        <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Data</th><th class="px-5 py-3">Descrição</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Valor</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse($transactions as $transaction)<tr><td class="px-5 py-4 whitespace-nowrap">{{ $transaction->competence_date->format('d/m/Y') }}</td><td class="px-5 py-4 font-medium">{{ $transaction->description }}</td><td class="px-5 py-4"><x-badge :tone="$transaction->status->value">{{ $transaction->status->label() }}</x-badge></td><td class="px-5 py-4 text-right font-bold"><x-money :value="$transaction->amount" /></td></tr>@empty<tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Nenhuma movimentação.</td></tr>@endforelse
        </tbody></table></div>
        <div class="px-5 py-4">{{ $transactions->links() }}</div>
    </div>
</x-app-layout>
