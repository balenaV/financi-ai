<x-app-layout>
    <x-slot name="title">Importar extrato</x-slot>
    <x-page-header title="Importar extrato" description="Traga seus lançamentos do banco sem digitar um por um." />

    <div
        id="import-wizard"
        class="mx-auto max-w-3xl space-y-5"
        data-store-url="{{ route('transactions.import.store') }}"
        data-transactions-url="{{ route('transactions.index') }}"
    >
        <ol class="flex flex-wrap items-center gap-2" data-steps>
            @foreach(['upload' => 'Arquivo', 'mapear' => 'Colunas', 'revisar' => 'Revisão', 'concluido' => 'Pronto'] as $key => $label)
                <li data-step-pill="{{ $key }}" class="flex items-center gap-2 rounded-full border border-(--border-subtle) px-3 py-1.5 text-sm font-semibold text-slate-500">
                    <span class="grid size-5 place-items-center rounded-full bg-slate-100 text-xs font-bold">{{ $loop->iteration }}</span>
                    {{ $label }}
                </li>
            @endforeach
        </ol>

        {{-- Etapa 1: upload --}}
        <section data-step="upload" class="surface space-y-5 p-6">
            <x-form.select name="account_id" label="Importar para" data-account-select required>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </x-form.select>

            <label data-dropzone class="flex min-h-[220px] cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-(--border-default) p-8 text-center transition-colors">
                <span class="grid size-14 place-items-center rounded-2xl bg-primary-50 text-primary-600"><i class="fa-solid fa-file-arrow-up text-xl"></i></span>
                <span class="text-lg font-extrabold tracking-tight">Arraste seu extrato aqui</span>
                <span class="max-w-md text-sm text-slate-500">Ou clique para escolher um arquivo. Aceitamos OFX e CSV, até 10 MB.</span>
                <span class="btn-primary mt-1">Escolher arquivo</span>
                <input type="file" accept=".ofx,.csv" data-file-input class="hidden">
            </label>

            <div data-file-info class="hidden items-center gap-3 rounded-xl border border-(--border-subtle) bg-(--bg-alt) p-4">
                <span class="grid size-9 place-items-center rounded-lg bg-white text-primary-600"><i class="fa-solid fa-file-lines"></i></span>
                <span class="min-w-0 flex-1">
                    <span data-file-name class="block truncate text-sm font-bold"></span>
                    <span data-file-meta class="block text-xs text-slate-500"></span>
                </span>
                <button type="button" data-action="clear-file" class="btn-ghost" aria-label="Remover arquivo"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <p class="flex items-start gap-2 text-sm text-slate-500"><i class="fa-solid fa-shield-halved mt-0.5 text-primary-600"></i>O arquivo é lido para extrair os lançamentos e descartado em seguida. O financiaí nunca pede a senha do seu banco.</p>

            <div class="flex justify-end">
                <button type="button" data-action="upload" class="btn-primary" disabled>Continuar<i class="fa-solid fa-arrow-right ml-1"></i></button>
            </div>
        </section>

        {{-- Etapa 2: mapear colunas (só aparece para CSV) --}}
        <section data-step="mapear" class="hidden space-y-5">
            <div class="surface space-y-5 p-6">
                <div>
                    <h2 class="text-lg font-extrabold tracking-tight">Conferir as colunas</h2>
                    <p class="mt-1 max-w-xl text-sm text-slate-500">Diga qual coluna do arquivo corresponde a cada campo — na próxima importação desse banco já vem preenchido.</p>
                </div>
                <div data-column-fields class="grid grid-cols-1 gap-4 sm:grid-cols-2"></div>
                <div class="grid grid-cols-1 gap-4 border-t border-(--border-subtle) pt-4 sm:grid-cols-2">
                    <x-form.select name="date_format" label="Formato de data" data-date-format>
                        <option value="DD/MM/AAAA">DD/MM/AAAA</option>
                        <option value="AAAA-MM-DD">AAAA-MM-DD</option>
                        <option value="MM/DD/AAAA">MM/DD/AAAA</option>
                    </x-form.select>
                    <x-form.select name="decimal_separator" label="Separador decimal" data-decimal-separator>
                        <option value="virgula">Vírgula (1.234,56)</option>
                        <option value="ponto">Ponto (1,234.56)</option>
                    </x-form.select>
                </div>
            </div>

            <div class="flex justify-between gap-3">
                <button type="button" data-action="back-upload" class="btn-secondary"><i class="fa-solid fa-arrow-left mr-1"></i>Voltar</button>
                <button type="button" data-action="parse" class="btn-primary">Ler lançamentos<i class="fa-solid fa-arrow-right ml-1"></i></button>
            </div>
        </section>

        {{-- Etapa 3: revisar --}}
        <section data-step="revisar" class="hidden space-y-4">
            <div data-parsing-state class="surface flex items-center gap-3 p-6 text-sm font-semibold text-slate-500">
                <i class="fa-solid fa-spinner fa-spin"></i>Lendo o arquivo...
            </div>

            <div data-review-content class="hidden space-y-4">
                <div data-summary class="grid grid-cols-1 gap-3 sm:grid-cols-3"></div>

                <div class="surface overflow-hidden !p-0">
                    <div class="flex items-center gap-3 border-b border-(--border-subtle) bg-(--bg-alt) px-5 py-3">
                        <input type="checkbox" data-select-all class="size-4 accent-(--brand-600)">
                        <span class="text-sm font-bold">Lançamento</span>
                        <span data-selected-text class="ml-auto text-sm text-slate-500"></span>
                    </div>
                    <div data-rows></div>
                </div>
            </div>

            <div class="flex justify-between gap-3">
                <button type="button" data-action="back-mapear" class="btn-secondary"><i class="fa-solid fa-arrow-left mr-1"></i>Voltar</button>
                <button type="button" data-action="commit" class="btn-primary" data-commit-label><i class="fa-solid fa-check mr-1"></i>Importar lançamentos</button>
            </div>
        </section>

        {{-- Etapa 4: concluído --}}
        <section data-step="concluido" class="hidden space-y-4">
            <div class="relative flex flex-wrap items-center gap-6 overflow-hidden rounded-2xl border border-(--border-subtle) p-8" style="background:#0D1410">
                <div class="min-w-[250px] flex-1">
                    <span class="inline-flex items-center gap-2 rounded-full bg-[rgba(56,193,114,0.14)] px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-[#38C172]"><i class="fa-solid fa-check"></i>Importação concluída</span>
                    <h2 data-final-title class="mt-3 text-2xl font-extrabold tracking-tight text-[#FFF6E6]"></h2>
                    <p data-final-detail class="mt-2 max-w-xl text-sm text-white/70"></p>
                </div>
                <img src="{{ asset('images/mascot/capi-comemorando.png') }}" alt="" class="ml-auto w-32 shrink-0">
            </div>

            <div data-final-summary class="grid grid-cols-1 gap-3 sm:grid-cols-3"></div>

            <div class="flex justify-between gap-3">
                <button type="button" data-action="restart" class="btn-secondary"><i class="fa-solid fa-rotate-left mr-1"></i>Importar outro extrato</button>
                <a href="{{ route('dashboard') }}" class="btn-primary">Ver meu painel<i class="fa-solid fa-arrow-right ml-1"></i></a>
            </div>
        </section>
    </div>

    <template data-row-template>
        <div class="flex items-center gap-3 border-b border-(--border-subtle) px-5 py-3 last:border-0" data-row>
            <input type="checkbox" data-row-check class="size-4 shrink-0 accent-(--brand-600)">
            <span data-row-date class="w-12 shrink-0 text-xs font-bold text-slate-500"></span>
            <span class="min-w-0 flex-1">
                <span class="flex items-center gap-2">
                    <span data-row-desc class="truncate text-sm font-semibold"></span>
                    <span data-row-badge></span>
                </span>
            </span>
            <select data-row-category class="form-control !mt-0 !w-40 !py-1.5 text-sm">
                <option value="">Sem categoria</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <span data-row-amount class="w-28 shrink-0 text-right text-sm font-bold"></span>
        </div>
    </template>
</x-app-layout>
