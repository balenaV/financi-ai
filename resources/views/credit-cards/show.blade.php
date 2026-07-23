<x-app-layout>
    <x-slot name="title">{{ $creditCard->name }}</x-slot>

    <x-page-header :title="$creditCard->name" :description="$creditCard->issuer.' · fechamento dia '.$creditCard->closing_day.' · vencimento dia '.$creditCard->due_day">
        <a href="{{ route('credit-cards.edit', $creditCard) }}" class="btn-secondary"><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar cartão</a>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-financial-card label="Faturas em aberto" :value="$summary['outstanding']" tone="negative" />
        <x-financial-card label="Limite do cartão" :value="$creditCard->credit_limit" />
        <x-financial-card label="Limite disponível" :value="$summary['available_limit']" tone="positive" />
        <x-financial-card label="Dívida total consolidada" :value="$debtSummary['total']" tone="negative" />
    </div>

    @if($creditCard->active)
        <section class="surface mt-6 p-6">
            <div class="mb-5">
                <p class="eyebrow">Fechamento mensal</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">Registrar fatura</h2>
                <p class="mt-1 text-sm text-slate-500">Informe o total fechado do mês, incluindo compras parceladas lançadas nesta fatura.</p>
            </div>
            <form method="POST" action="{{ route('credit-cards.bills.store', $creditCard) }}" class="grid items-end gap-4 md:grid-cols-2 xl:grid-cols-[1fr_1fr_1fr_2fr_auto]">
                @csrf
                <x-form.input label="Mês de referência" name="reference_month" type="month" :value="old('reference_month', now()->format('Y-m'))" required />
                <x-form.input label="Valor total" name="total_amount" data-money-input inputmode="decimal" required />
                <x-form.input label="Vencimento" name="due_date" type="date" :value="old('due_date', now()->day(min(now()->daysInMonth, $creditCard->due_day))->format('Y-m-d'))" required />
                <x-form.input label="Observação" name="notes" placeholder="Ex.: inclui 3/10 do notebook" />
                <x-button type="submit"><i class="fa-solid fa-plus" aria-hidden="true"></i> Registrar</x-button>
            </form>
        </section>
    @endif

    <section class="surface mt-6 overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div><h2 class="font-bold">Faturas mensais</h2><p class="text-sm text-slate-500">Histórico e pagamentos deste cartão.</p></div>
            <i class="fa-solid fa-file-invoice-dollar text-xl text-primary-500" aria-hidden="true"></i>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[920px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr><th class="px-5 py-3">Referência</th><th class="px-5 py-3">Vencimento</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Total</th><th class="px-5 py-3">Pagamento</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($creditCard->bills as $bill)
                        <tr>
                            <td class="px-5 py-4 font-semibold">{{ $bill->reference_month->translatedFormat('F/Y') }}</td>
                            <td class="px-5 py-4">{{ $bill->due_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-4"><x-badge :tone="$bill->status->value">{{ $bill->status->label() }}</x-badge></td>
                            <td class="px-5 py-4 text-right font-bold"><x-money :value="$bill->total_amount" /></td>
                            <td class="px-5 py-4">
                                @if($bill->status->value === 'paid')
                                    <span class="font-semibold text-accent-800"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Paga em {{ $bill->paid_at->format('d/m/Y') }}</span>
                                @elseif($bill->status->value !== 'cancelled')
                                    <form method="POST" action="{{ route('credit-card-bills.pay', $bill) }}" class="flex items-center gap-2" data-confirm="Confirmar pagamento da fatura e criar a despesa?">
                                        @csrf
                                        <select name="account_id" class="form-control !mt-0 !min-w-40" required><option value="">Conta</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected($accounts->count() === 1)>{{ $account->name }}</option>@endforeach</select>
                                        <input type="date" name="paid_at" value="{{ today()->format('Y-m-d') }}" class="form-control !mt-0 !w-auto">
                                        <button class="btn-primary !min-h-9"><i class="fa-solid fa-check" aria-hidden="true"></i> Pagar</button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($editableBillIds->contains($bill->id))
                                    <a href="{{ route('credit-card-bills.edit', $bill) }}" class="mr-3 text-primary-600" aria-label="Editar fatura"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                @endif
                                @if($bill->status->value !== 'paid')
                                    <form method="POST" action="{{ route('credit-card-bills.destroy', $bill) }}" data-confirm="Excluir esta fatura?">@csrf @method('DELETE')<button class="text-red-700" aria-label="Excluir fatura"><i class="fa-solid fa-trash" aria-hidden="true"></i></button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Nenhuma fatura registrada para este cartão.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
