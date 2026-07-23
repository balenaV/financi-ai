@php
    $userSettings = auth()->user()->settings()->firstOrCreate();
    $nav = [
        ['route' => 'dashboard', 'match' => 'dashboard', 'label' => 'Visão geral', 'icon' => 'fa-chart-pie'],
        ['route' => 'transactions.index', 'match' => 'transactions.*', 'label' => 'Transações', 'icon' => 'fa-arrow-right-arrow-left'],
        ['route' => 'accounts.index', 'match' => 'accounts.*', 'label' => 'Contas', 'icon' => 'fa-wallet'],
        ['route' => 'categories.index', 'match' => 'categories.*', 'label' => 'Categorias', 'icon' => 'fa-tags'],
        ['route' => 'credit-cards.index', 'match' => 'credit-cards.*', 'label' => 'Cartões', 'icon' => 'fa-credit-card'],
        ['route' => 'debts.index', 'match' => 'debts.*', 'label' => 'Empréstimos', 'icon' => 'fa-building-columns'],
        ['route' => 'investments.index', 'match' => 'investments.*', 'label' => 'Investimentos', 'icon' => 'fa-chart-line'],
        ['route' => 'budgets.index', 'match' => 'budgets.*', 'label' => 'Orçamentos', 'icon' => 'fa-bullseye'],
        ['route' => 'goals.index', 'match' => 'goals.*', 'label' => 'Metas', 'icon' => 'fa-flag-checkered'],
        ['route' => 'reports.index', 'match' => 'reports.*', 'label' => 'Relatórios', 'icon' => 'fa-chart-column'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $userSettings->theme === 'dark' ? 'dark' : '' }}" data-theme="{{ $userSettings->theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' — ' : '' }}{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/financi-ai-symbol.png') }}">
    <script>
        try {
            localStorage.setItem('financi-theme', @json($userSettings->theme));
            if (localStorage.getItem('financi-sidebar-collapsed') === 'true') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        } catch (error) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="min-h-screen">
    <aside id="desktop-sidebar" class="fixed inset-y-0 left-0 z-40 hidden border-r border-slate-200 bg-white lg:flex lg:flex-col">
        <div class="flex h-20 items-center justify-between gap-2 px-3">
            <span data-sidebar-full-brand><x-application-logo size="sidebar" /></span>
            <span data-sidebar-compact-brand class="hidden"><x-application-logo compact /></span>
            <button type="button" data-desktop-sidebar-toggle class="grid size-9 shrink-0 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-primary-700" aria-label="Recolher menu lateral" aria-expanded="true" title="Recolher menu">
                <i class="fa-solid fa-angles-left" aria-hidden="true"></i>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-6" aria-label="Navegação principal">
            @foreach($nav as $item)
                <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}" class="{{ request()->routeIs($item['match']) ? 'bg-primary-50 text-primary-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold">
                    <i class="fa-solid {{ $item['icon'] }} w-5 shrink-0 text-center text-base text-primary-500" aria-hidden="true"></i>
                    <span data-sidebar-label>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="border-t border-slate-200 p-4">
            <a href="{{ route('settings.edit') }}" title="Configurações" class="flex items-center gap-3 rounded-xl p-2 hover:bg-slate-50">
                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-primary-800 text-xs font-bold text-white">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}</span>
                <span data-sidebar-label class="min-w-0"><span class="block truncate text-sm font-semibold">{{ auth()->user()->name }}</span><span class="block truncate text-xs text-slate-500">Configurações</span></span>
            </a>
        </div>
    </aside>

    <div id="app-content">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
            <button type="button" data-sidebar-open class="grid size-10 place-items-center rounded-xl hover:bg-slate-100 lg:hidden" aria-label="Abrir menu">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <div class="lg:hidden"><x-application-logo compact /></div>
            <div class="ml-auto flex items-center gap-2">
                <button type="button" id="toggle-theme" data-url="{{ route('settings.toggle-theme') }}" class="btn-secondary !px-3 cursor-pointer" aria-label="{{ $userSettings->theme === 'dark' ? 'Ativar modo claro' : 'Ativar modo noturno' }}" data-tooltip="{{ $userSettings->theme === 'dark' ? 'Modo claro' : 'Modo noturno' }}">
                    <i class="fa-solid {{ $userSettings->theme === 'dark' ? 'fa-sun' : 'fa-moon' }}" aria-hidden="true"></i>
                </button>
                <button type="button" id="toggle-values" data-url="{{ route('settings.toggle-values') }}" data-hidden="{{ $userSettings->hide_values ? 'true' : 'false' }}" class="btn-secondary !px-3 cursor-pointer" aria-label="{{ $userSettings->hide_values ? 'Exibir valores' : 'Ocultar valores' }}" data-tooltip="{{ $userSettings->hide_values ? 'Exibir valores' : 'Ocultar valores' }}">
                    <i class="fa-solid {{ $userSettings->hide_values ? 'fa-eye-slash' : 'fa-eye' }}" aria-hidden="true"></i>
                </button>
                <a href="{{ route('transactions.create') }}" class="btn-primary"><i class="fa-solid fa-plus" aria-hidden="true"></i><span class="hidden sm:inline">Nova transação</span></a>
            </div>
        </header>

        <main class="px-4 py-6 pb-28 sm:px-6 lg:px-8 lg:pb-10">
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <p class="font-semibold">Revise os campos informados.</p>
                    <ul class="mt-1 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>

    <div id="mobile-sidebar" class="fixed inset-0 z-50 hidden lg:hidden">
        <button class="absolute inset-0 bg-slate-950/60" data-sidebar-close aria-label="Fechar menu"></button>
        <div class="relative h-full w-[min(20rem,86vw)] bg-white p-4 shadow-2xl">
            <div class="mb-6 flex items-center justify-between"><x-application-logo size="sidebar" /><button data-sidebar-close class="grid size-10 place-items-center rounded-xl hover:bg-slate-100" aria-label="Fechar menu"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></div>
            <nav class="space-y-1">
                @foreach($nav as $item)
                    <a href="{{ route($item['route']) }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs($item['match']) ? 'bg-primary-50 text-primary-800' : 'text-slate-600' }}">
                        <i class="fa-solid {{ $item['icon'] }} w-5 text-center text-primary-500" aria-hidden="true"></i>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-5 border-t border-slate-200 bg-white px-2 pb-[max(.5rem,env(safe-area-inset-bottom))] pt-2 lg:hidden" aria-label="Navegação móvel">
        <a href="{{ route('dashboard') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 text-[11px] font-semibold {{ request()->routeIs('dashboard') ? 'text-primary-600' : 'text-slate-500' }}"><i class="fa-solid fa-house" aria-hidden="true"></i><span>Início</span></a>
        <a href="{{ route('transactions.index') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 text-[11px] font-semibold {{ request()->routeIs('transactions.index') ? 'text-primary-600' : 'text-slate-500' }}"><i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i><span>Transações</span></a>
        <a href="{{ route('transactions.create') }}" class="mx-auto grid size-12 place-items-center rounded-full bg-primary-600 text-xl text-white shadow-lg" aria-label="Nova transação"><i class="fa-solid fa-plus" aria-hidden="true"></i></a>
        <a href="{{ route('credit-cards.index') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 text-[11px] font-semibold {{ request()->routeIs('credit-cards.*') ? 'text-primary-600' : 'text-slate-500' }}"><i class="fa-solid fa-credit-card" aria-hidden="true"></i><span>Cartões</span></a>
        <button type="button" data-sidebar-open class="flex min-h-12 flex-col items-center justify-center gap-1 text-[11px] font-semibold text-slate-500"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i><span>Menu</span></button>
    </nav>
</div>

<div id="toast-container" class="fixed right-4 top-20 z-[70] space-y-2" aria-live="polite">
    @foreach(['success', 'error'] as $kind)
        @if(session($kind))<div class="toast max-w-sm rounded-xl border {{ $kind === 'success' ? 'border-accent-400/30 bg-accent-50 text-accent-800' : 'border-red-200 bg-red-50 text-red-800' }} px-4 py-3 text-sm font-medium shadow-lg">{{ session($kind) }}</div>@endif
    @endforeach
</div>

<x-modal name="confirm-modal" title="Confirmar ação">
    <p id="confirm-message" class="text-sm text-slate-600">Deseja continuar?</p>
    <div class="mt-6 flex justify-end gap-2"><x-button variant="secondary" data-modal-close>Cancelar</x-button><x-button variant="danger" id="confirm-submit">Confirmar</x-button></div>
</x-modal>
</body>
</html>
