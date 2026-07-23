<x-app-layout>
    <x-slot name="title">Empréstimos e dívidas</x-slot>
    <x-page-header title="Empréstimos e dívidas" description="Acompanhe empréstimos, financiamentos e o total consolidado com suas faturas.">
        <a href="{{ route('debts.create') }}" class="btn-primary"><i class="fa-solid fa-plus" aria-hidden="true"></i> Nova dívida</a>
    </x-page-header>
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-financial-card label="Dívida total consolidada" :value="$debtSummary['total']" tone="negative" />
        <x-financial-card label="Empréstimos em aberto" :value="$debtSummary['loans']" />
        <article class="surface p-5"><p class="text-sm text-slate-500">Faturas em aberto</p><p class="mt-2 text-2xl font-bold text-red-700"><x-money :value="$debtSummary['cards']" /></p><a href="{{ route('credit-cards.index') }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-primary-600"><i class="fa-solid fa-credit-card" aria-hidden="true"></i> Ver cartões</a></article>
    </div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($debts as $row)
            @php($debt = $row['debt'])<article class="surface p-5">
                <div class="flex justify-between gap-3"><div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ match($debt->kind ?? 'loan') {'loan' => 'Empréstimo bancário', 'financing' => 'Financiamento', 'agreement' => 'Acordo', default => 'Outra dívida'} }} · {{ $debt->creditor }}</p><h2 class="mt-1 font-bold text-slate-950">{{ $debt->name }}</h2></div><x-badge :tone="$debt->status->value">{{ $debt->status->label() }}</x-badge></div>
                <div class="mt-5 grid grid-cols-2 gap-3"><div><p class="text-xs text-slate-500">Saldo devedor</p><p class="font-bold text-red-700"><x-money :value="$row['summary']['remaining']" /></p></div><div><p class="text-xs text-slate-500">Total pago</p><p class="font-bold text-accent-800"><x-money :value="$row['summary']['paid']" /></p></div></div>
                <div class="mt-5"><div class="mb-2 flex justify-between text-xs"><span>{{ $row['summary']['paid_count'] }}/{{ $debt->installment_count }} parcelas</span><strong>{{ $row['summary']['percentage'] }}%</strong></div><x-progress :value="$row['summary']['percentage']" tone="success" /></div>
                @if($row['summary']['overdue_count'])<p class="mt-4 rounded-lg bg-red-50 p-2 text-xs font-semibold text-red-700">{{ $row['summary']['overdue_count'] }} parcela(s) atrasada(s)</p>@endif
                <div class="mt-5 flex justify-between border-t border-slate-100 pt-4"><a href="{{ route('debts.edit', $debt) }}" class="text-sm font-semibold text-slate-600">Editar</a><a href="{{ route('debts.show', $debt) }}" class="text-sm font-semibold text-primary-600">Ver parcelas →</a></div>
            </article>
        @empty
            <div class="md:col-span-2 xl:col-span-3"><x-empty-state title="Nenhuma dívida cadastrada" message="Cadastre financiamentos, empréstimos ou acordos para visualizar o saldo devedor." /></div>
        @endforelse
    </div><div class="mt-6">{{ $debts->links() }}</div>
</x-app-layout>
