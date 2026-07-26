<x-app-layout>
    <x-slot name="title">Transações</x-slot>
    <x-page-header title="Transações" description="Consulte, filtre e organize todas as movimentações.">
        <a href="{{ route('transactions.import.create') }}" class="btn-secondary"><i class="fa-solid fa-file-import" aria-hidden="true"></i> Importar</a>
        <a href="{{ route('transactions.export', request()->query()) }}" class="btn-secondary">Exportar CSV</a>
        <a href="{{ route('transactions.create') }}" class="btn-primary">＋ Nova transação</a>
    </x-page-header>

    <form method="GET" class="surface mb-5 p-4" data-filter-form>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
            <x-form.input label="Buscar" name="search" :value="$filters['search'] ?? null" placeholder="Descrição" />
            <x-form.input label="De" name="start_date" type="date" :value="$filters['start_date'] ?? null" />
            <x-form.input label="Até" name="end_date" type="date" :value="$filters['end_date'] ?? null" />
            <x-form.select label="Tipo" name="type"><option value="">Todos</option>@foreach($types as $type)<option value="{{ $type->value }}" @selected(($filters['type'] ?? null) === $type->value)>{{ $type->label() }}</option>@endforeach</x-form.select>
            <x-form.select label="Status" name="status"><option value="">Todos</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>@endforeach</x-form.select>
            <x-form.select label="Conta" name="account_id"><option value="">Todas</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected(($filters['account_id'] ?? null) == $account->id)>{{ $account->name }}</option>@endforeach</x-form.select>
            <x-form.select label="Categoria" name="category_id"><option value="">Todas</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) == $category->id)>{{ $category->name }}</option>@endforeach</x-form.select>
            <x-form.input label="Valor mínimo" name="min_amount" type="number" step="0.01" :value="$filters['min_amount'] ?? null" />
            <x-form.input label="Valor máximo" name="max_amount" type="number" step="0.01" :value="$filters['max_amount'] ?? null" />
            <x-form.select label="Ordenar" name="sort"><option value="newest">Mais recentes</option><option value="oldest" @selected(($filters['sort'] ?? null) === 'oldest')>Mais antigas</option><option value="amount_desc" @selected(($filters['sort'] ?? null) === 'amount_desc')>Maior valor</option><option value="amount_asc" @selected(($filters['sort'] ?? null) === 'amount_asc')>Menor valor</option></x-form.select>
            <div class="flex items-end gap-2 sm:col-span-2"><x-button type="submit" class="flex-1">Aplicar filtros</x-button><a href="{{ route('transactions.index') }}" class="btn-secondary">Limpar</a></div>
        </div>
    </form>

    <div class="surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[880px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Data</th><th class="px-5 py-3">Descrição</th><th class="px-5 py-3">Conta</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Valor</th><th class="px-5 py-3 text-right">Ações</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $transaction)
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-5 py-4 whitespace-nowrap">{{ $transaction->competence_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-4"><div class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl {{ $transaction->type->value === 'income' ? 'bg-accent-50 text-accent-800' : ($transaction->type->value === 'expense' ? 'bg-red-50 text-red-700' : 'bg-primary-50 text-primary-800') }}">{{ $transaction->type->value === 'income' ? '↑' : ($transaction->type->value === 'expense' ? '↓' : '↔') }}</span><span><strong class="block max-w-[18rem] truncate">{{ $transaction->description }}</strong><small class="text-slate-500">{{ $transaction->category?->name ?? $transaction->type->label() }}@if($transaction->installment_total) · {{ $transaction->installment_number }}/{{ $transaction->installment_total }}@endif</small></span></div></td>
                        <td class="px-5 py-4">
                            @if($transaction->payment_channel === 'credit_card')
                                <span class="inline-flex items-center gap-2"><i class="fa-solid fa-credit-card text-primary-500" aria-hidden="true"></i> {{ $transaction->creditCard?->name ?? 'Cartão' }}</span>
                                @if($transaction->creditCardBill)<small class="block text-slate-500">Fatura {{ $transaction->creditCardBill->reference_month->format('m/Y') }}</small>@endif
                            @else
                                {{ $transaction->account?->name ?? 'Sem conta' }}@if($transaction->destinationAccount)<span class="block text-xs text-slate-500">→ {{ $transaction->destinationAccount->name }}</span>@endif
                            @endif
                        </td>
                        <td class="px-5 py-4"><x-badge :tone="$transaction->status->value">{{ $transaction->status->label() }}</x-badge></td>
                        <td class="px-5 py-4 text-right font-bold {{ $transaction->type->value === 'income' ? 'text-accent-800' : ($transaction->type->value === 'expense' ? 'text-red-700' : 'text-slate-700') }}"><x-money :value="$transaction->amount" /></td>
                        <td class="px-5 py-4"><div class="flex justify-end gap-1">
                            <a href="{{ route('transactions.edit', $transaction) }}" class="btn !min-h-9 !px-3 text-primary-600 hover:bg-primary-50">Editar</a>
                            <form method="POST" action="{{ route('transactions.duplicate', $transaction) }}">@csrf<button class="btn !min-h-9 !px-3 text-slate-600 hover:bg-slate-100" data-tooltip="Duplicar">⧉</button></form>
                            @if($transaction->status->value !== 'cancelled')<form method="POST" action="{{ route('transactions.cancel', $transaction) }}" data-confirm="Cancelar esta transação?">@csrf @method('PATCH')<button class="btn !min-h-9 !px-3 text-amber-700 hover:bg-amber-50">Cancelar</button></form>@endif
                            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" data-confirm="Excluir permanentemente esta transação?">@csrf @method('DELETE')<button class="btn !min-h-9 !px-3 text-red-600 hover:bg-red-50">Excluir</button></form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-14 text-center"><h3 class="font-semibold">Nenhuma transação encontrada</h3><p class="mt-1 text-slate-500">Ajuste os filtros ou registre uma nova movimentação.</p></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $transactions->links() }}</div>
    </div>
</x-app-layout>
