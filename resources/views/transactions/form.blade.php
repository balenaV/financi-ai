<x-app-layout>
    <x-slot name="title">{{ $transaction->exists ? 'Editar transação' : 'Nova transação' }}</x-slot>
    <x-page-header :title="$transaction->exists ? 'Editar transação' : 'Nova transação'" description="Registre entradas, saídas, transferências, parcelas ou recorrências." />
    <form method="POST" action="{{ $transaction->exists ? route('transactions.update', $transaction) : route('transactions.store') }}" class="surface mx-auto max-w-4xl p-6" id="transaction-form">
        @csrf @if($transaction->exists) @method('PUT') @endif
        @php
            $singleAccountId = $accounts->count() === 1 ? $accounts->first()->id : null;
            $selectedChannel = old('payment_channel', $transaction->payment_channel ?? 'account');
        @endphp
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.select label="Tipo" name="type" id="transaction-type" required>
                @foreach($types as $type)<option value="{{ $type->value }}" @selected(old('type', $transaction->type?->value ?? request('type', 'expense')) === $type->value)>{{ $type->label() }}</option>@endforeach
            </x-form.select>
            <x-form.input label="Descrição" name="description" :value="$transaction->description" placeholder="Ex.: Supermercado" required />
            <div data-payment-channel-field>
                <x-form.select label="Meio de pagamento" name="payment_channel" id="payment-channel" required>
                    <option value="account" @selected($selectedChannel === 'account')>Conta, dinheiro ou Pix</option>
                    <option value="credit_card" @selected($selectedChannel === 'credit_card')>Cartão de crédito</option>
                </x-form.select>
            </div>
            <div data-account-field>
                <x-form.select label="Conta de origem" name="account_id">
                    <option value="">Selecione</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" @selected(old('account_id', $transaction->account_id ?? request('account_id') ?? $singleAccountId) == $account->id)>{{ $account->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div data-credit-card-field class="hidden">
                <x-form.select label="Cartão de crédito" name="credit_card_id">
                    <option value="">Selecione</option>
                    @foreach($creditCards as $creditCard)
                        <option value="{{ $creditCard->id }}" @selected(old('credit_card_id', $transaction->credit_card_id) == $creditCard->id)>
                            {{ $creditCard->name }} · •••• {{ $creditCard->last_four }}
                        </option>
                    @endforeach
                </x-form.select>
                <p class="mt-1 text-xs text-slate-500">A compra será somada automaticamente à fatura correta conforme a data de fechamento.</p>
            </div>
            <div data-transfer-field class="hidden"><x-form.select label="Conta de destino" name="destination_account_id"><option value="">Selecione</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected(old('destination_account_id', $transaction->destination_account_id) == $account->id)>{{ $account->name }}</option>@endforeach</x-form.select></div>
            <div data-category-field><x-form.select label="Categoria" name="category_id"><option value="">Selecione</option>@foreach($categories as $category)<option value="{{ $category->id }}" data-type="{{ $category->type->value }}" @selected(old('category_id', $transaction->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</x-form.select></div>
            <x-form.input label="Valor" name="amount" data-money-input inputmode="decimal" :value="$transaction->amount" placeholder="0,00" required />
            <x-form.input label="Data de competência" name="competence_date" type="date" :value="old('competence_date', $transaction->competence_date?->format('Y-m-d') ?? today()->format('Y-m-d'))" required />
            <x-form.input label="Vencimento" name="due_date" type="date" :value="old('due_date', $transaction->due_date?->format('Y-m-d'))" />
            <x-form.input label="Data do pagamento" name="paid_at" type="date" :value="old('paid_at', $transaction->paid_at?->format('Y-m-d'))" />
            <x-form.select label="Status" name="status" required>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $transaction->status?->value ?? 'planned') === $status->value)>{{ $status->label() }}</option>@endforeach</x-form.select>
            @unless($transaction->exists)
                <x-form.select label="Forma de pagamento" name="payment_mode" id="payment-mode"><option value="single">Pagamento único</option><option value="installment" @selected(old('payment_mode') === 'installment')>Parcelado</option></x-form.select>
                <div data-installment-fields class="hidden sm:col-span-2">
                    <div class="grid gap-5 rounded-xl border border-primary-200 bg-primary-50 p-4 sm:grid-cols-2">
                        <x-form.input label="Quantidade de parcelas" name="installment_count" type="number" min="2" max="240" :value="old('installment_count', 2)" />
                        <x-form.input label="Data da primeira parcela" name="first_installment_date" type="date" :value="old('first_installment_date', today()->format('Y-m-d'))" />
                        <p class="sm:col-span-2 text-xs text-primary-800">Os centavos são distribuídos com exatidão; eventual diferença fica na última parcela.</p>
                    </div>
                </div>
                <div data-recurrence-fields class="sm:col-span-2">
                    <div class="grid gap-5 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2">
                        <x-form.input label="Repetições mensais" name="recurrence_count" type="number" min="1" max="120" :value="old('recurrence_count', 1)" />
                        <x-form.input label="Data inicial da recorrência" name="recurrence_start_date" type="date" :value="old('recurrence_start_date', today()->format('Y-m-d'))" />
                    </div>
                </div>
            @endunless
            <div class="sm:col-span-2"><x-form.textarea label="Observações" name="notes" :value="$transaction->notes" /></div>
            @if($transaction->exists && ($transaction->installment_group_id || $transaction->recurrence_group_id))
                <label class="sm:col-span-2 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4"><input type="checkbox" name="update_future" value="1" class="mt-0.5 size-5 rounded"><span><strong class="block text-sm text-amber-900">Atualizar ocorrências futuras</strong><small class="text-amber-800">Transações já efetivadas não serão alteradas.</small></span></label>
            @endif
        </div>
        <div class="mt-7 flex justify-end gap-2"><a href="{{ route('transactions.index') }}" class="btn-secondary">Cancelar</a><x-button type="submit" data-loading-text="Salvando...">{{ $transaction->exists ? 'Salvar alterações' : 'Criar transação' }}</x-button></div>
    </form>
</x-app-layout>
