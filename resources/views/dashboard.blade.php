<x-app-layout>
    <x-slot name="title">Visão geral</x-slot>
    <x-page-header title="Visão geral" description="Seu panorama financeiro, sem ruído.">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="month" class="form-control !mt-0 !w-auto" aria-label="Mês">
                @foreach(range(1, 12) as $month)<option value="{{ $month }}" @selected(($filters['month'] ?? now()->month) == $month)>{{ \Carbon\Carbon::create(null, $month)->translatedFormat('F') }}</option>@endforeach
            </select>
            <select name="year" class="form-control !mt-0 !w-auto" aria-label="Ano">
                @foreach(range(now()->year - 3, now()->year + 1) as $year)<option @selected(($filters['year'] ?? now()->year) == $year)>{{ $year }}</option>@endforeach
            </select>
            <select name="account_id" class="form-control !mt-0 !w-auto" aria-label="Conta">
                <option value="">Todas as contas</option>
                @foreach($filterAccounts as $account)<option value="{{ $account->id }}" @selected(($filters['account_id'] ?? null) == $account->id)>{{ $account->name }}</option>@endforeach
            </select>
            <x-button type="submit" variant="secondary">Filtrar</x-button>
        </form>
    </x-page-header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-financial-card label="Saldo atual" :value="$dashboard['summary']['balance_current']" tone="primary" hint="Somente movimentações efetivadas" />
        <x-financial-card label="Saldo projetado" :value="$dashboard['summary']['balance_projected']" hint="Inclui planejadas e vencidas" />
        <x-financial-card label="Receitas no período" :value="$dashboard['summary']['income']" tone="positive" />
        <x-financial-card label="Despesas no período" :value="$dashboard['summary']['expense']" tone="negative" />
        <x-financial-card label="Receitas previstas" :value="$dashboard['summary']['forecast_income']" tone="positive" hint="Efetivadas + planejadas no período" />
        <x-financial-card label="Despesas previstas" :value="$dashboard['summary']['forecast_expense']" tone="negative" hint="Efetivadas + planejadas no período" />
        <x-financial-card label="Resultado previsto" :value="$dashboard['summary']['forecast_result']" :tone="bccomp($dashboard['summary']['forecast_result'], '0', 2) >= 0 ? 'positive' : 'negative'" hint="Receitas menos despesas previstas" />
        <x-financial-card label="Resultado" :value="$dashboard['summary']['result']" :tone="bccomp($dashboard['summary']['result'], '0', 2) >= 0 ? 'positive' : 'negative'" :hint="'Taxa de economia: '.$dashboard['summary']['savings_rate'].'%'" />
        <x-financial-card label="Dívida total consolidada" :value="$dashboard['summary']['debt_total']" tone="negative" hint="Faturas + parcelas de empréstimos" />
        <x-financial-card label="Investimentos" :value="$dashboard['summary']['invested']" tone="positive" />
        <x-financial-card label="Patrimônio líquido" :value="$dashboard['summary']['net_worth']" tone="primary" />
    </section>

    @if($dashboard['summary']['overdue_count'] || $dashboard['summary']['upcoming_count'] || $dashboard['summary']['overdue_bill_count'])
        <section class="mt-5 grid gap-3 sm:grid-cols-2">
            @if($dashboard['summary']['overdue_count'])
                <a href="{{ route('transactions.index', ['status' => 'overdue']) }}" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><strong>{{ $dashboard['summary']['overdue_count'] }} transação(ões) vencida(s).</strong> Revise agora.</a>
            @endif
            @if($dashboard['summary']['upcoming_count'])
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"><strong>{{ $dashboard['summary']['upcoming_count'] }} vencimento(s)</strong> nos próximos 15 dias.</div>
            @endif
            @if($dashboard['summary']['overdue_bill_count'])
                <a href="{{ route('credit-cards.index') }}" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><strong>{{ $dashboard['summary']['overdue_bill_count'] }} fatura(s) de cartão vencida(s).</strong> Ver cartões.</a>
            @endif
        </section>
    @endif

    <section class="mt-6 grid gap-5 xl:grid-cols-2">
        <article class="surface p-5">
            <div class="mb-4"><h2 class="font-bold text-slate-900">Receitas versus despesas</h2><p class="text-sm text-slate-500">Últimos seis meses</p></div>
            <div class="h-72"><canvas data-chart='@json($chartConfigs["cashflow"])'></canvas></div>
        </article>
        <article class="surface p-5">
            <div class="mb-4"><h2 class="font-bold text-slate-900">Evolução do saldo</h2><p class="text-sm text-slate-500">Saldo efetivado ao fim de cada mês</p></div>
            <div class="h-72"><canvas data-chart='@json($chartConfigs["balance"])'></canvas></div>
        </article>
        <article class="surface p-5">
            <div class="mb-4"><h2 class="font-bold text-slate-900">Despesas por categoria</h2><p class="text-sm text-slate-500">Onde seu dinheiro foi usado</p></div>
            <div class="h-72"><canvas data-chart='@json($chartConfigs["categories"])'></canvas></div>
        </article>
        <article class="surface p-5">
            <div class="mb-4"><h2 class="font-bold text-slate-900">Distribuição de investimentos</h2><p class="text-sm text-slate-500">Valor atual por tipo</p></div>
            <div class="h-72"><canvas data-chart='@json($chartConfigs["investments"])'></canvas></div>
        </article>
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[1.35fr_.65fr]">
        <article class="surface overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><h2 class="font-bold">Movimentações recentes</h2><a href="{{ route('transactions.index') }}" class="text-sm font-semibold text-primary-600">Ver todas</a></div>
            @forelse($dashboard['recent'] as $transaction)
                <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-3 last:border-0">
                    <span class="grid size-9 place-items-center rounded-xl {{ $transaction->type->value === 'income' ? 'bg-accent-50 text-accent-800' : ($transaction->type->value === 'expense' ? 'bg-red-50 text-red-700' : 'bg-primary-50 text-primary-800') }}">{{ $transaction->type->value === 'income' ? '↑' : ($transaction->type->value === 'expense' ? '↓' : '↔') }}</span>
                    <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ $transaction->description }}</p><p class="text-xs text-slate-500">{{ $transaction->account?->name ?? $transaction->creditCard?->name ?? 'Sem conta' }} · {{ $transaction->competence_date->format('d/m/Y') }}</p></div>
                    <p class="text-sm font-bold {{ $transaction->type->value === 'income' ? 'text-accent-800' : ($transaction->type->value === 'expense' ? 'text-red-700' : 'text-slate-700') }}"><x-money :value="$transaction->amount" /></p>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-500">Nenhuma movimentação ainda.</div>
            @endforelse
        </article>
        <article class="surface p-5">
            <h2 class="font-bold">Resumo por conta</h2>
            <div class="mt-4 space-y-4">
                @forelse($dashboard['accounts'] as $row)
                    <a href="{{ route('accounts.show', $row['account']) }}" class="flex items-center gap-3">
                        <span class="size-3 rounded-full" style="background: {{ $row['account']->color }}"></span>
                        <span class="min-w-0 flex-1 truncate text-sm font-medium">{{ $row['account']->name }}</span>
                        <span class="text-sm font-bold"><x-money :value="$row['current']" /></span>
                    </a>
                @empty<p class="text-sm text-slate-500">Cadastre sua primeira conta.</p>@endforelse
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-5 lg:grid-cols-2">
        <article class="surface p-5">
            <div class="flex justify-between"><h2 class="font-bold">Metas em andamento</h2><a href="{{ route('goals.index') }}" class="text-sm font-semibold text-primary-600">Gerenciar</a></div>
            <div class="mt-4 space-y-5">
                @forelse($dashboard['goals'] as $goal)
                    @php $goalCurrent = $goal->use_account_balance && $goal->account ? app(\App\Services\AccountBalanceService::class)->current($goal->account) : $goal->current_amount; $percentage = \App\Support\Money::percentage($goalCurrent, $goal->target_amount); @endphp
                    <div><div class="mb-2 flex justify-between text-sm"><span class="font-semibold">{{ $goal->name }}</span><span>{{ $percentage }}%</span></div><x-progress :value="$percentage" tone="success" /></div>
                @empty<p class="text-sm text-slate-500">Nenhuma meta ativa.</p>@endforelse
            </div>
        </article>
        <article class="surface p-5">
            <div class="flex justify-between"><h2 class="font-bold">Orçamentos do mês</h2><a href="{{ route('budgets.index') }}" class="text-sm font-semibold text-primary-600">Gerenciar</a></div>
            <div class="mt-4 space-y-5">
                @forelse($dashboard['budgets'] as $row)
                    <div><div class="mb-2 flex justify-between text-sm"><span class="font-semibold">{{ $row['budget']->category->name }}</span><span>{{ $row['metrics']['percentage'] }}%</span></div><x-progress :value="$row['metrics']['percentage']" :tone="$row['metrics']['level']" /></div>
                @empty<p class="text-sm text-slate-500">Nenhum orçamento neste mês.</p>@endforelse
            </div>
        </article>
    </section>
</x-app-layout>
