@php
    $features = [
        [
            'icon' => 'fa-calendar-check',
            'title' => 'Planejamento que olha adiante',
            'description' => 'Registre receitas e despesas futuras, acompanhe recorrências e visualize o impacto dos próximos meses.',
        ],
        [
            'icon' => 'fa-scale-balanced',
            'title' => 'Precisão em cada centavo',
            'description' => 'Parcelas, saldos e transferências são calculados sem arredondamentos que distorcem a sua realidade.',
        ],
        [
            'icon' => 'fa-shield-halved',
            'title' => 'Seus dados, seu controle',
            'description' => 'Cada conta possui dados isolados, histórico de segurança e ferramentas para exportar suas informações.',
        ],
    ];

    $stats = [
        ['value' => '12 meses', 'label' => 'de visão futura'],
        ['value' => '100%', 'label' => 'dos dados isolados'],
        ['value' => '1 painel', 'label' => 'para toda a vida financeira'],
    ];

    $essentialFeatures = [
        'Contas e transações',
        'Categorias personalizadas',
        'Parcelamentos e recorrências',
        'Importação e exportação de extratos',
    ];

    $planningFeatures = [
        'Faturas de cartão e empréstimos',
        'Receitas e despesas futuras',
        'Orçamentos e metas',
        'Investimentos e relatórios',
    ];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FFFDF2">
    <meta name="description" content="Organize contas, transações, cartões, dívidas, investimentos, orçamentos e metas com clareza e precisão.">
    <title>{{ config('app.name') }} — Clareza para suas finanças</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/financi-ai-symbol.svg') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <script>
        try {
            const theme = localStorage.getItem('financi-theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (error) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="marketing-shell min-h-screen w-full overflow-x-hidden bg-background text-foreground selection:bg-primary-100 selection:text-primary-900">
        <header class="sticky top-0 z-50 w-full border-b border-foreground/15 bg-background/90 backdrop-blur-md">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 sm:px-8">
                <a href="{{ url('/') }}" aria-label="financi.ai — página inicial">
                    <x-application-logo />
                </a>

                <nav class="hidden items-center gap-8 text-sm font-medium text-foreground-secondary md:flex" aria-label="Navegação principal">
                    <a href="#recursos" class="transition-colors hover:text-foreground">Recursos</a>
                    <a href="#visao-completa" class="transition-colors hover:text-foreground">Visão completa</a>
                    <a href="#comecar" class="transition-colors hover:text-foreground">Começar</a>
                </nav>

                <div class="hidden items-center gap-3 md:flex">
                    <button type="button" data-landing-theme class="btn !size-10 !min-h-10 !p-0" aria-label="Alternar tema" title="Alternar tema">
                        <i class="fa-solid fa-moon dark:hidden" aria-hidden="true"></i>
                        <i class="fa-solid fa-sun hidden dark:inline" aria-hidden="true"></i>
                    </button>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary">
                            Abrir painel <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-2 text-sm font-medium text-foreground transition-colors hover:text-primary-600">Entrar</a>
                        @if(config('features.registration'))
                            <a href="{{ route('register') }}" class="btn-primary">
                                Começar grátis <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        @endif
                    @endauth
                </div>

                <details class="group relative md:hidden">
                    <summary class="btn grid !size-10 !min-h-10 list-none !rounded-full !border !border-foreground !p-0 marker:content-none" aria-label="Abrir menu">
                        <i class="fa-solid fa-bars group-open:hidden" aria-hidden="true"></i>
                        <i class="fa-solid fa-xmark hidden group-open:inline" aria-hidden="true"></i>
                    </summary>
                    <div class="surface absolute right-0 top-12 w-64 p-3 shadow-lg">
                        <nav class="flex flex-col gap-1 text-sm font-medium" aria-label="Navegação móvel">
                            <a href="#recursos" class="rounded-lg px-3 py-2.5 text-foreground-secondary hover:bg-background-alt hover:text-foreground">Recursos</a>
                            <a href="#visao-completa" class="rounded-lg px-3 py-2.5 text-foreground-secondary hover:bg-background-alt hover:text-foreground">Visão completa</a>
                            <a href="#comecar" class="rounded-lg px-3 py-2.5 text-foreground-secondary hover:bg-background-alt hover:text-foreground">Começar</a>
                        </nav>
                        <div class="mt-3 grid gap-2 border-t border-border pt-3">
                            <button type="button" data-landing-theme class="btn-secondary w-full">
                                <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i> Alternar tema
                            </button>
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn-primary w-full">Abrir painel</a>
                            @else
                                <a href="{{ route('login') }}" class="btn-secondary w-full">Entrar</a>
                                @if(config('features.registration'))
                                    <a href="{{ route('register') }}" class="btn-primary w-full">Criar conta</a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </details>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden pb-28 pt-20 sm:pt-24">
                <div class="pointer-events-none absolute left-1/2 top-1/3 size-[38rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-500/[.08] blur-[120px] dark:bg-primary-500/[.06]"></div>

                <div class="relative z-10 mx-auto max-w-4xl px-5 text-center sm:px-8">
                    <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-foreground px-4 py-2 text-sm font-medium text-foreground">
                        <span class="size-2 rounded-full bg-primary-500" aria-hidden="true"></span>
                        Clareza para decidir melhor todos os dias
                    </div>

                    <h1 class="text-5xl font-bold leading-[1.08] tracking-tight text-foreground md:text-7xl">
                        Suas finanças,<br>
                        organizadas para o <span class="text-primary-600 dark:text-primary-400">futuro.</span>
                    </h1>

                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-foreground-secondary md:text-xl">
                        Entenda o presente, planeje os próximos meses e acompanhe seu patrimônio em uma experiência segura, precisa e feita para a sua rotina.
                    </p>

                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn-primary w-full !rounded-2xl !px-8 !py-4 !text-base sm:w-auto">
                                Ir para visão geral <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        @else
                            <a href="{{ config('features.registration') ? route('register') : route('login') }}" class="btn-primary w-full !rounded-2xl !px-8 !py-4 !text-base sm:w-auto">
                                Começar agora <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        @endauth
                        <a href="#recursos" class="btn-secondary w-full !rounded-2xl !px-8 !py-4 !text-base sm:w-auto">
                            Conhecer recursos
                        </a>
                    </div>
                </div>

                <div class="relative z-10 mx-auto mt-20 max-w-6xl px-4 sm:px-8" data-reveal>
                    <div class="overflow-hidden rounded-t-3xl border border-b-0 border-foreground/35 bg-background-alt dark:bg-surface-elevated">
                        <div class="flex h-10 items-center gap-2 border-b border-foreground/20 bg-surface px-4">
                            <span class="size-3 rounded-full border border-foreground"></span>
                            <span class="size-3 rounded-full border border-foreground"></span>
                            <span class="size-3 rounded-full border border-foreground bg-primary-500"></span>
                            <span class="ml-3 hidden text-xs font-medium text-foreground-tertiary sm:inline">Visão geral — financi.ai</span>
                        </div>

                        <div class="flex min-h-[31rem]">
                            <aside class="hidden w-64 shrink-0 flex-col border-r border-foreground/20 bg-surface p-4 md:flex">
                                <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-foreground-tertiary">Visão geral</p>
                                <nav class="space-y-1">
                                    <span class="flex items-center gap-3 rounded-xl bg-primary-50 px-3 py-2.5 text-sm font-semibold text-primary-600 dark:text-primary-400">
                                        <i class="fa-solid fa-chart-pie w-4 text-center" aria-hidden="true"></i> Dashboard
                                    </span>
                                    <span class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-foreground-secondary">
                                        <i class="fa-solid fa-arrow-right-arrow-left w-4 text-center" aria-hidden="true"></i> Transações
                                    </span>
                                    <span class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-foreground-secondary">
                                        <i class="fa-solid fa-calendar-days w-4 text-center" aria-hidden="true"></i> Planejamento
                                    </span>
                                    <span class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-foreground-secondary">
                                        <i class="fa-solid fa-chart-line w-4 text-center" aria-hidden="true"></i> Investimentos
                                    </span>
                                </nav>

                                <div class="mt-auto rounded-2xl border border-border bg-background p-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-lightbulb text-primary-600 dark:text-primary-400" aria-hidden="true"></i>
                                        <strong class="text-xs text-foreground">Planejamento</strong>
                                    </div>
                                    <p class="mt-2 text-xs leading-relaxed text-foreground-secondary">Você possui três compromissos previstos para os próximos 30 dias.</p>
                                    <span class="mt-3 block rounded-lg border border-foreground bg-primary-500 px-3 py-2 text-center text-xs font-semibold text-black">Ver próximos meses</span>
                                </div>
                            </aside>

                            <div class="flex min-w-0 flex-1 flex-col gap-5 p-4 sm:p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h2 class="text-xl font-semibold text-foreground">Olá, Gabriel</h2>
                                        <p class="mt-1 text-sm text-foreground-secondary">Aqui está o resumo da sua vida financeira.</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <span class="grid size-9 place-items-center rounded-full border border-border bg-surface text-foreground-secondary">
                                            <i class="fa-solid fa-magnifying-glass text-xs" aria-hidden="true"></i>
                                        </span>
                                        <span class="relative grid size-9 place-items-center rounded-full border border-border bg-surface text-foreground-secondary">
                                            <i class="fa-solid fa-bell text-xs" aria-hidden="true"></i>
                                            <span class="absolute right-2 top-2 size-1.5 rounded-full bg-primary-500"></span>
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <article class="surface p-4">
                                        <p class="text-xs font-medium text-foreground-secondary">Patrimônio líquido</p>
                                        <p class="mt-2 text-xl font-bold tracking-tight text-foreground sm:text-2xl">R$ 48.750,00</p>
                                        <p class="mt-2 text-xs font-medium text-success-600 dark:text-success-400"><i class="fa-solid fa-arrow-trend-up mr-1" aria-hidden="true"></i> +8,4% no período</p>
                                    </article>
                                    <article class="surface p-4">
                                        <p class="text-xs font-medium text-foreground-secondary">Resultado do mês</p>
                                        <p class="mt-2 text-xl font-bold tracking-tight text-foreground sm:text-2xl">R$ 3.570,00</p>
                                        <p class="mt-2 text-xs font-medium text-success-600 dark:text-success-400"><i class="fa-solid fa-circle-check mr-1" aria-hidden="true"></i> Saldo positivo</p>
                                    </article>
                                    <article class="surface p-4">
                                        <p class="text-xs font-medium text-foreground-secondary">Compromissos futuros</p>
                                        <p class="mt-2 text-xl font-bold tracking-tight text-foreground sm:text-2xl">R$ 2.480,00</p>
                                        <p class="mt-2 text-xs font-medium text-warning-600 dark:text-warning-500"><i class="fa-solid fa-calendar mr-1" aria-hidden="true"></i> Próximos 30 dias</p>
                                    </article>
                                </div>

                                <article class="surface flex min-h-52 flex-1 flex-col p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <h3 class="text-sm font-semibold text-foreground">Evolução patrimonial</h3>
                                            <p class="mt-1 text-xs text-foreground-tertiary">Últimos 12 meses</p>
                                        </div>
                                        <div class="flex rounded-lg bg-background-alt p-1 text-xs text-foreground-secondary">
                                            <span class="px-2 py-1">3M</span>
                                            <span class="rounded-md bg-surface px-2 py-1 font-semibold text-foreground shadow-xs">1A</span>
                                        </div>
                                    </div>
                                    <div class="mt-5 flex min-h-36 flex-1 items-end gap-2">
                                        @foreach([32, 39, 46, 42, 55, 61, 58, 70, 76, 72, 85, 94] as $height)
                                            <div class="group relative flex-1 rounded-t-md bg-primary-100" style="height: {{ $height }}%">
                                                <div class="absolute inset-x-0 bottom-0 rounded-t-md bg-primary-500 transition-opacity group-hover:opacity-80" style="height: 68%"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="recursos" class="bg-background py-24">
                <div class="mx-auto max-w-7xl px-5 sm:px-8">
                    <div class="mx-auto mb-16 max-w-2xl text-center" data-reveal>
                        <h2 class="text-3xl font-bold tracking-tight text-foreground md:text-4xl">Clareza de verdade,<br>não apenas gráficos bonitos.</h2>
                        <p class="mt-4 text-lg leading-relaxed text-foreground-secondary">Cada recurso existe para transformar números dispersos em decisões financeiras mais simples.</p>
                    </div>

                    <div class="grid gap-8 md:grid-cols-3">
                        @foreach($features as $feature)
                            <article class="editorial-card group p-8 transition duration-200 hover:-translate-x-0.5 hover:-translate-y-0.5 hover:shadow-[4px_4px_0_var(--text-primary)]" data-reveal data-reveal-delay="{{ $loop->index }}">
                                <span class="grid size-12 place-items-center rounded-full border border-foreground bg-primary-100 text-xl text-primary-700 dark:bg-primary-500 dark:text-black">
                                    <i class="fa-solid {{ $feature['icon'] }}" aria-hidden="true"></i>
                                </span>
                                <h3 class="mt-6 text-xl font-semibold tracking-tight text-foreground">{{ $feature['title'] }}</h3>
                                <p class="mt-3 leading-relaxed text-foreground-secondary">{{ $feature['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="border-y border-foreground/20 bg-background-alt py-16 dark:bg-surface-elevated">
                <div class="mx-auto max-w-7xl px-5 sm:px-8">
                    <div class="grid divide-y divide-foreground/20 text-center md:grid-cols-3 md:divide-x md:divide-y-0">
                        @foreach($stats as $stat)
                            <div class="py-6 md:py-0">
                                <p class="text-4xl font-bold tracking-tight text-foreground">{{ $stat['value'] }}</p>
                                <p class="mt-2 text-sm font-medium uppercase tracking-wider text-foreground-secondary">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="visao-completa" class="bg-background py-24">
                <div class="mx-auto max-w-7xl px-5 sm:px-8">
                    <div class="mx-auto mb-16 max-w-2xl text-center" data-reveal>
                        <h2 class="text-3xl font-bold tracking-tight text-foreground md:text-4xl">Do primeiro lançamento à visão completa.</h2>
                        <p class="mt-4 text-lg text-foreground-secondary">Comece pelo essencial e avance para um planejamento conectado, sem trocar de ferramenta.</p>
                    </div>

                    <div class="mx-auto grid max-w-4xl gap-8 md:grid-cols-2" data-reveal>
                        <article class="editorial-card p-8 transition hover:-translate-x-0.5 hover:-translate-y-0.5 hover:shadow-[4px_4px_0_var(--text-primary)]">
                            <h3 class="text-lg font-semibold text-foreground">Organização essencial</h3>
                            <p class="mt-2 text-4xl font-bold tracking-tight text-foreground">Comece simples</p>
                            <p class="my-8 border-b border-border pb-8 text-sm leading-relaxed text-foreground-secondary">Tudo para registrar e entender sua movimentação diária.</p>
                            <ul class="mb-8 space-y-4">
                                @foreach($essentialFeatures as $feature)
                                    <li class="flex items-center gap-3 text-sm font-medium text-foreground-secondary">
                                        <i class="fa-solid fa-check text-success-500" aria-hidden="true"></i> {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ auth()->check() ? route('dashboard') : (config('features.registration') ? route('register') : route('login')) }}" class="btn-secondary w-full">Organizar minhas finanças</a>
                        </article>

                        <article class="editorial-card relative !border-2 !border-primary-500 p-8 transition hover:-translate-x-0.5 hover:-translate-y-0.5 hover:shadow-[4px_4px_0_var(--brand-500)]">
                            <span class="absolute right-8 top-0 -translate-y-1/2 rounded-full border border-foreground bg-primary-500 px-4 py-1.5 text-xs font-bold text-black">Visão completa</span>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-foreground">Planejamento integrado</h3>
                                <i class="fa-solid fa-sparkles text-primary-600 dark:text-primary-400" aria-hidden="true"></i>
                            </div>
                            <p class="mt-2 text-4xl font-bold tracking-tight text-foreground">Veja além do mês</p>
                            <p class="my-8 border-b border-border pb-8 text-sm leading-relaxed text-foreground-secondary">Conecte compromissos, patrimônio e objetivos em uma única visão.</p>
                            <ul class="mb-8 space-y-4">
                                @foreach($planningFeatures as $feature)
                                    <li class="flex items-center gap-3 text-sm font-medium text-foreground">
                                        <i class="fa-solid fa-check text-primary-600 dark:text-primary-400" aria-hidden="true"></i> {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ auth()->check() ? route('dashboard') : (config('features.registration') ? route('register') : route('login')) }}" class="btn-primary w-full">Ter visão completa</a>
                        </article>
                    </div>
                </div>
            </section>

            <section id="comecar" class="relative overflow-hidden border-y border-primary-500 bg-[#090A0B] py-24 text-[#FFFDF2]">
                <div class="pointer-events-none absolute right-0 top-0 size-[32rem] -translate-y-1/2 translate-x-1/2 rounded-full border-[80px] border-primary-500/20"></div>
                <div class="relative z-10 mx-auto max-w-4xl px-5 text-center sm:px-8" data-reveal>
                    <h2 class="text-3xl font-bold tracking-tight md:text-5xl">Pronto para enxergar suas finanças com <span class="text-primary-500">clareza?</span></h2>
                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-[#FFFDF2]/75 md:text-xl">Crie sua conta, registre sua realidade financeira e transforme seus próximos passos em um plano objetivo.</p>
                    <a href="{{ auth()->check() ? route('dashboard') : (config('features.registration') ? route('register') : route('login')) }}" class="btn-primary mt-10 !min-h-12 !rounded-2xl !px-8 !py-4 !text-lg">
                        {{ auth()->check() ? 'Abrir meu painel' : 'Criar conta gratuitamente' }}
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </section>
        </main>

        <footer class="bg-black py-16 text-[#FFFDF2] dark:bg-[#050709]">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div class="mb-12 grid gap-12 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <x-application-logo variant="wordmark-white" />
                        <p class="mt-6 max-w-sm text-sm leading-relaxed text-[#FFFDF2]/70">Controle financeiro privado, preciso e preparado para acompanhar todas as fases da sua vida.</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white">Produto</h3>
                        <ul class="mt-4 space-y-3 text-sm text-[#FFFDF2]/70">
                            <li><a href="#recursos" class="transition hover:text-white">Recursos</a></li>
                            <li><a href="#visao-completa" class="transition hover:text-white">Visão completa</a></li>
                            <li><a href="#comecar" class="transition hover:text-white">Começar</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white">Acesso</h3>
                        <ul class="mt-4 space-y-3 text-sm text-[#FFFDF2]/70">
                            @auth
                                <li><a href="{{ route('dashboard') }}" class="transition hover:text-white">Abrir painel</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="transition hover:text-white">Entrar</a></li>
                                @if(config('features.registration'))
                                    <li><a href="{{ route('register') }}" class="transition hover:text-white">Criar conta</a></li>
                                @endif
                            @endauth
                        </ul>
                    </div>
                </div>
                <div class="flex flex-col items-center justify-between gap-4 border-t border-[#FFFDF2]/10 pt-8 text-xs text-[#FFFDF2]/50 md:flex-row">
                    <p>&copy; {{ now()->year }} financi.ai. Todos os direitos reservados.</p>
                    <p>Feito para decisões financeiras mais claras.</p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        document.querySelectorAll('[data-landing-theme]').forEach((button) => {
            button.addEventListener('click', () => {
                const dark = document.documentElement.classList.toggle('dark');
                const theme = dark ? 'dark' : 'light';
                document.documentElement.dataset.theme = theme;
                try {
                    localStorage.setItem('financi-theme', theme);
                } catch (error) {}
                document.querySelector('meta[name="theme-color"]')?.setAttribute('content', dark ? '#080A0D' : '#FFFDF2');
            });
        });
    </script>
</body>
</html>
