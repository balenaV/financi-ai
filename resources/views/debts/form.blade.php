<x-app-layout>
    <x-slot name="title">{{ $debt->exists ? 'Editar dívida' : 'Nova dívida' }}</x-slot>
    <x-page-header :title="$debt->exists ? 'Editar dívida' : 'Nova dívida'" description="As parcelas são geradas com distribuição exata dos centavos." />
    <form method="POST" action="{{ $debt->exists ? route('debts.update', $debt) : route('debts.store') }}" class="surface mx-auto max-w-4xl p-6">@csrf @if($debt->exists) @method('PUT') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input label="Nome" name="name" :value="$debt->name" required /><x-form.input label="Credor" name="creditor" :value="$debt->creditor" required />
            <x-form.select label="Tipo da dívida" name="kind" required><option value="loan" @selected(old('kind', $debt->kind ?? 'loan') === 'loan')>Empréstimo bancário</option><option value="financing" @selected(old('kind', $debt->kind) === 'financing')>Financiamento</option><option value="agreement" @selected(old('kind', $debt->kind) === 'agreement')>Acordo ou renegociação</option><option value="other" @selected(old('kind', $debt->kind) === 'other')>Outra dívida</option></x-form.select>
            <x-form.input label="Valor original" name="original_amount" data-money-input inputmode="decimal" :value="$debt->original_amount" required /><x-form.input label="Total previsto" name="expected_total_amount" data-money-input inputmode="decimal" :value="$debt->expected_total_amount" required />
            <x-form.input label="Taxa de juros (%)" name="interest_rate" type="number" step="0.0001" :value="$debt->interest_rate" /><x-form.input label="Data inicial" name="started_at" type="date" :value="old('started_at', $debt->started_at?->format('Y-m-d') ?? today()->format('Y-m-d'))" required />
            @if($debt->exists)
                <input type="hidden" name="installment_count" value="{{ $debt->installment_count }}"><input type="hidden" name="due_date" value="{{ $debt->due_date?->format('Y-m-d') }}"><div><span class="form-label">Parcelas</span><p class="form-control bg-slate-50">{{ $debt->installment_count }} (não alterável após gerar)</p></div><div><span class="form-label">Vencimento final</span><p class="form-control bg-slate-50">{{ $debt->due_date?->format('d/m/Y') }}</p></div>
            @else
                <x-form.input label="Quantidade de parcelas" name="installment_count" type="number" min="1" max="600" :value="old('installment_count', 1)" required /><x-form.input label="Primeiro vencimento" name="first_due_date" type="date" :value="old('first_due_date', today()->addMonth()->format('Y-m-d'))" required />
            @endif
            <x-form.select label="Status" name="status" required>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $debt->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>@endforeach</x-form.select>
            <div class="sm:col-span-2"><x-form.textarea label="Descrição" name="description" :value="$debt->description" /></div><div class="sm:col-span-2"><x-form.textarea label="Observações" name="notes" :value="$debt->notes" /></div>
        </div>
        <div class="mt-7 flex justify-end gap-2"><a href="{{ route('debts.index') }}" class="btn-secondary">Cancelar</a><x-button type="submit">Salvar dívida</x-button></div>
    </form>
</x-app-layout>
