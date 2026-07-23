<x-app-layout>
    <x-slot name="title">{{ $creditCard->exists ? 'Editar cartão' : 'Novo cartão' }}</x-slot>

    <x-page-header :title="$creditCard->exists ? 'Editar cartão' : 'Novo cartão'" description="Cadastre os dados essenciais para organizar fechamentos, vencimentos e limite." />

    <form method="POST" action="{{ $creditCard->exists ? route('credit-cards.update', $creditCard) : route('credit-cards.store') }}" class="surface mx-auto max-w-3xl p-6">
        @csrf
        @if($creditCard->exists) @method('PUT') @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input label="Nome do cartão" name="name" :value="$creditCard->name" placeholder="Ex.: Cartão principal" required />
            <x-form.input label="Banco ou emissor" name="issuer" :value="$creditCard->issuer" required />
            <x-form.input label="Últimos 4 dígitos" name="last_four" inputmode="numeric" maxlength="4" :value="$creditCard->last_four" />
            <x-form.input label="Limite total" name="credit_limit" data-money-input inputmode="decimal" :value="$creditCard->credit_limit" required />
            <x-form.input label="Dia de fechamento" name="closing_day" type="number" min="1" max="31" :value="old('closing_day', $creditCard->closing_day ?? 2)" required />
            <x-form.input label="Dia de vencimento" name="due_day" type="number" min="1" max="31" :value="old('due_day', $creditCard->due_day ?? 10)" required />
            <x-form.input label="Cor do cartão" name="color" type="color" :value="old('color', $creditCard->color ?? '#534ab7')" required />
            <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" class="size-5 rounded" @checked(old('active', $creditCard->exists ? $creditCard->active : true))>
                <span><strong class="block text-sm">Cartão ativo</strong><small class="text-slate-500">Permite cadastrar novas faturas</small></span>
            </label>
            <div class="sm:col-span-2"><x-form.textarea label="Observações" name="notes" :value="$creditCard->notes" /></div>
        </div>

        <div class="mt-7 flex justify-end gap-2">
            <a href="{{ route('credit-cards.index') }}" class="btn-secondary">Cancelar</a>
            <x-button type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Salvar cartão</x-button>
        </div>
    </form>
</x-app-layout>
