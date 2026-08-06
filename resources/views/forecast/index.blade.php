<x-app-layout>
    <x-slot name="title">Planejamento futuro</x-slot>
    <x-page-header title="Planejamento futuro" description="Veja o que você espera receber e pagar nos próximos meses.">
        <a href="{{ route('dashboard') }}" class="btn-primary">
            <i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Adicionar ganho futuro
        </a>
        <a href="{{ route('dashboard') }}" class="btn-secondary">
            <i class="fa-solid fa-calendar-minus" aria-hidden="true"></i> Adicionar despesa futura
        </a>
    </x-page-header>

    <section class="grid gap-4 sm:grid-cols-3">
        <x-financial-card label="Receitas futuras" :value="$summary['income']" tone="positive" hint="Todos os lançamentos planejados" />
        <x-financial-card label="Despesas futuras" :value="$summary['expense']" tone="negative" hint="Todos os lançamentos planejados" />
        <x-financial-card label="Resultado projetado" :value="$summary['result']" :tone="bccomp($summary['result'], '0', 2) >= 0 ? 'positive' : 'negative'" />
    </section>

    <section class="surface mt-6 overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-bold">Projeção dos próximos 12 meses</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[620px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Mês</th><th class="px-5 py-3 text-right">Receitas</th><th class="px-5 py-3 text-right">Despesas</th><th class="px-5 py-3 text-right">Resultado</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($monthly as $row)
                        <tr>
                            <td class="px-5 py-4 font-semibold capitalize">{{ $row['month']->translatedFormat('F/Y') }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-accent-800"><x-money :value="$row['income']" /></td>
                            <td class="px-5 py-4 text-right font-semibold text-red-700"><x-money :value="$row['expense']" /></td>
                            <td class="px-5 py-4 text-right font-bold {{ bccomp($row['result'], '0', 2) >= 0 ? 'text-accent-800' : 'text-red-700' }}"><x-money :value="$row['result']" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Nenhum lançamento futuro nos próximos 12 meses.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="surface mt-6 overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-bold">Lançamentos futuros</h2></div>
        <div class="divide-y divide-slate-100">
            @forelse($transactions as $transaction)
                <div class="flex flex-wrap items-center gap-3 px-5 py-4">
                    <span class="grid size-10 place-items-center rounded-xl {{ $transaction->type === \App\Enums\TransactionType::Income ? 'bg-accent-50 text-accent-800' : 'bg-red-50 text-red-700' }}">
                        <i class="fa-solid {{ $transaction->type === \App\Enums\TransactionType::Income ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}" aria-hidden="true"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <strong class="block truncate">{{ $transaction->description }}</strong>
                        <span class="text-xs text-slate-500">{{ $transaction->competence_date->format('d/m/Y') }} · {{ $transaction->account?->name ?? 'Sem conta' }}</span>
                    </div>
                    <span class="font-bold {{ $transaction->type === \App\Enums\TransactionType::Income ? 'text-accent-800' : 'text-red-700' }}"><x-money :value="$transaction->amount" /></span>
                </div>
            @empty
                <div class="p-10 text-center text-sm text-slate-500">Cadastre um ganho ou uma despesa futura para começar sua projeção.</div>
            @endforelse
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $transactions->links() }}</div>
    </section>
</x-app-layout>
