<x-app-layout>
    <x-slot name="title">Cartões e faturas</x-slot>

    <x-page-header title="Cartões e faturas" description="Registre as faturas mensais e acompanhe o impacto delas na sua dívida total.">
        <a href="{{ route('credit-cards.create') }}" class="btn-primary"><i class="fa-solid fa-plus" aria-hidden="true"></i> Novo cartão</a>
    </x-page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-financial-card label="Dívida total consolidada" :value="$debtSummary['total']" tone="negative" />
        <x-financial-card label="Faturas em aberto" :value="$debtSummary['cards']" tone="negative" />
        <x-financial-card label="Empréstimos em aberto" :value="$debtSummary['loans']" />
    </div>

    @if($debtSummary['overdue_bills'])
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
            {{ $debtSummary['overdue_bills'] }} fatura(s) vencida(s) precisam de atenção.
        </div>
    @endif

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($cards as $card)
            @php($summary = $summaries[$card->id])
            <article class="surface overflow-hidden">
                <div class="p-5 text-white" style="background: linear-gradient(135deg, {{ $card->color }}, #26215c)">
                    <div class="flex items-start justify-between">
                        <i class="fa-solid fa-credit-card text-2xl" aria-hidden="true"></i>
                        <x-badge :tone="$card->active ? 'active' : 'cancelled'">{{ $card->active ? 'Ativo' : 'Inativo' }}</x-badge>
                    </div>
                    <p class="mt-8 text-xs font-semibold uppercase tracking-[.18em] text-white/70">{{ $card->issuer }}</p>
                    <h2 class="mt-1 text-xl font-bold">{{ $card->name }}</h2>
                    @if($card->last_four)<p class="mt-3 font-mono tracking-[.28em] text-white/80">•••• {{ $card->last_four }}</p>@endif
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div><p class="text-xs text-slate-500">Faturas em aberto</p><p class="mt-1 font-bold text-red-700"><x-money :value="$summary['outstanding']" /></p></div>
                        <div><p class="text-xs text-slate-500">Limite disponível</p><p class="mt-1 font-bold text-primary-800"><x-money :value="$summary['available_limit']" /></p></div>
                    </div>
                    @if($summary['next_bill'])
                        <p class="mt-4 rounded-xl bg-slate-50 p-3 text-sm text-slate-600">Próximo vencimento: <strong>{{ $summary['next_bill']->due_date->format('d/m/Y') }}</strong></p>
                    @endif
                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                        <a href="{{ route('credit-cards.edit', $card) }}" class="text-sm font-semibold text-slate-600"><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</a>
                        <a href="{{ route('credit-cards.show', $card) }}" class="text-sm font-semibold text-primary-600">Ver faturas <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </div>
            </article>
        @empty
            <div class="md:col-span-2 xl:col-span-3">
                <x-empty-state title="Nenhum cartão cadastrado" message="Cadastre seu primeiro cartão para controlar faturas e vencimentos mensais." />
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $cards->links() }}</div>
</x-app-layout>
