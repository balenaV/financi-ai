<x-app-layout>
    <x-slot name="title">{{ $account->exists ? 'Editar conta' : 'Nova conta' }}</x-slot>
    <x-page-header :title="$account->exists ? 'Editar conta' : 'Nova conta'" description="Defina o ponto inicial; o saldo passa a ser calculado automaticamente." />
    <form method="POST" action="{{ $account->exists ? route('accounts.update', $account) : route('accounts.store') }}" class="surface mx-auto max-w-3xl p-6">
        @csrf @if($account->exists) @method('PUT') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input label="Nome" name="name" :value="$account->name" required />
            <x-form.select label="Tipo" name="type" required><option value="">Selecione</option>@foreach($types as $type)<option value="{{ $type->value }}" @selected(old('type', $account->type?->value) === $type->value)>{{ $type->label() }}</option>@endforeach</x-form.select>
            <x-form.input label="Instituição" name="institution" :value="$account->institution" />
            <x-form.input label="Saldo inicial" name="initial_balance" inputmode="decimal" data-money-input :value="$account->initial_balance ?? '0,00'" required />
            <x-form.input label="Data do saldo inicial" name="initial_balance_date" type="date" :value="old('initial_balance_date', $account->initial_balance_date?->format('Y-m-d') ?? today()->format('Y-m-d'))" required />
            <x-form.select label="Moeda" name="currency" required><option value="BRL" @selected(old('currency', $account->currency ?? 'BRL') === 'BRL')>BRL — Real</option><option value="USD" @selected(old('currency', $account->currency) === 'USD')>USD — Dólar</option></x-form.select>
            <x-form.input label="Cor" name="color" type="color" :value="$account->color ?? '#534ab7'" required class="h-12" />
            <x-form.select label="Ícone" name="icon" required>@foreach(['wallet'=>'Carteira','bank'=>'Banco','cash'=>'Dinheiro','card'=>'Cartão','chart'=>'Investimento'] as $value => $label)<option value="{{ $value }}" @selected(old('icon', $account->icon ?? 'wallet') === $value)>{{ $label }}</option>@endforeach</x-form.select>
            <div class="sm:col-span-2"><x-form.textarea label="Observações" name="notes" :value="$account->notes" /></div>
            <label class="flex items-center gap-3"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" class="size-5 rounded border-slate-300 text-primary-600" @checked(old('active', $account->exists ? $account->active : true))><span class="text-sm font-medium">Conta ativa</span></label>
        </div>
        <div class="mt-7 flex justify-end gap-2"><a href="{{ route('accounts.index') }}" class="btn-secondary">Cancelar</a><x-button type="submit">{{ $account->exists ? 'Salvar alterações' : 'Criar conta' }}</x-button></div>
    </form>
</x-app-layout>
