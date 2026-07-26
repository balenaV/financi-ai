<x-app-layout>
    <x-slot name="title">Importar transações</x-slot>
    <x-page-header title="Importar extrato" description="Envie CSV ou OFX; lançamentos repetidos serão ignorados automaticamente." />

    <form method="POST" action="{{ route('transactions.import.store') }}" enctype="multipart/form-data" class="surface max-w-2xl p-6">
        @csrf
        <div class="space-y-5">
            <x-form.select label="Conta de destino" name="account_id" required>
                <option value="">Selecione</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" @selected(old('account_id', $accounts->count() === 1 ? $accounts->first()->id : null) == $account->id)>{{ $account->name }}</option>
                @endforeach
            </x-form.select>
            <x-form.select label="Categoria padrão (opcional)" name="category_id">
                <option value="">Sem categoria</option>
                @foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>@endforeach
            </x-form.select>
            <x-form.input label="Arquivo CSV ou OFX" name="file" type="file" accept=".csv,.ofx,text/csv,application/x-ofx" required />
            <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                <strong class="text-slate-800">CSV aceito:</strong> cabeçalho com Data, Descrição e Valor. O valor negativo vira despesa; positivo vira receita. Datas podem usar DD/MM/AAAA ou AAAA-MM-DD.
            </div>
            <div class="flex gap-3"><x-button type="submit">Importar extrato</x-button><a href="{{ route('transactions.index') }}" class="btn-secondary">Cancelar</a></div>
        </div>
    </form>
</x-app-layout>
