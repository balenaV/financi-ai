<x-app-layout>
    <x-slot name="title">Investimentos</x-slot>
    <x-page-header title="Investimentos" description="Acompanhe sua carteira com atualizações e operações manuais."><a href="{{ route('investments.create') }}" class="btn-primary">＋ Novo investimento</a></x-page-header>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($investments as $row) @php($investment = $row['investment'])
            <article class="surface p-5"><div class="flex justify-between"><div><p class="text-xs font-semibold uppercase text-slate-500">{{ $investment->type->label() }}</p><h2 class="mt-1 font-bold">{{ $investment->name }}</h2><p class="text-xs text-slate-500">{{ $investment->institution }}{{ $investment->ticker ? ' · '.$investment->ticker : '' }}</p></div><x-badge :tone="$investment->status->value">{{ $investment->status->label() }}</x-badge></div>
            <p class="mt-6 text-xs text-slate-500">Valor atual</p><p class="mt-1 text-2xl font-bold text-primary-900"><x-money :value="$investment->current_amount" /></p>
            <div class="mt-4 flex justify-between text-sm"><span>Investido: <strong><x-money :value="$investment->invested_amount" /></strong></span><span class="{{ bccomp($row['metrics']['profit'], '0', 2) >= 0 ? 'text-accent-800' : 'text-red-700' }}">{{ $row['metrics']['return_percentage'] }}%</span></div>
            <div class="mt-5 flex justify-between border-t border-slate-100 pt-4"><span class="text-xs text-slate-500">Atualizado {{ $investment->last_updated_at->format('d/m/Y') }}</span><a href="{{ route('investments.show', $investment) }}" class="text-sm font-semibold text-primary-600">Detalhes →</a></div></article>
        @empty<div class="md:col-span-2 xl:col-span-3"><x-empty-state title="Nenhum investimento" message="Cadastre seus ativos para acompanhar rentabilidade e patrimônio." /></div>@endforelse
    </div><div class="mt-6">{{ $investments->links() }}</div>
</x-app-layout>
