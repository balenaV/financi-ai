<x-app-layout>
    <x-slot name="title">Editar fatura</x-slot>

    <x-page-header
        title="Editar fatura"
        :description="$bill->creditCard->name.' · fecha em '.$closingDate->format('d/m/Y')"
    />

    <form method="POST" action="{{ route('credit-card-bills.update', $bill) }}" class="surface mx-auto max-w-2xl p-6">
        @csrf
        @method('PATCH')

        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4 text-sm text-primary-800">
            Esta fatura ainda está aberta. Você pode corrigir o total conforme novas compras forem lançadas.
        </div>

        <div class="mt-5 grid gap-5">
            <div>
                <span class="form-label">Referência</span>
                <p class="form-control bg-slate-50">{{ $bill->reference_month->translatedFormat('F/Y') }}</p>
            </div>
            <x-form.input label="Valor total" name="total_amount" data-money-input inputmode="decimal" :value="$bill->total_amount" required />
            <x-form.textarea label="Observações" name="notes" :value="$bill->notes" />
        </div>

        <div class="mt-7 flex justify-end gap-2">
            <a href="{{ route('credit-cards.show', $bill->credit_card_id) }}" class="btn-secondary">Cancelar</a>
            <x-button type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Salvar fatura</x-button>
        </div>
    </form>
</x-app-layout>
