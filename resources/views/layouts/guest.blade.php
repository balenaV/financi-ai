<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ request()->routeIs('login') ? 'auth-login-root' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#090A0B">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/financi-ai-symbol.svg') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <script>
        try {
            if (localStorage.getItem('financi-theme') === 'dark') document.documentElement.classList.add('dark');
        } catch (error) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page {{ request()->routeIs('login') ? 'auth-page-login' : '' }}">
    <main class="auth-shell">
        <section class="auth-form-pane">
            <div class="auth-form-wrap">
                <a href="{{ url('/') }}" class="auth-brand" aria-label="Voltar para o início">
                    <x-application-logo size="auth" />
                </a>

                <div class="auth-form-card">
                    {{ $slot }}
                </div>

                <p class="auth-security-note">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                    Seus dados são protegidos e nunca são compartilhados.
                </p>
            </div>
        </section>

        <aside class="auth-story" aria-label="Visão do produto">
            <div class="auth-story-grid" aria-hidden="true"></div>
            <div class="auth-story-orbit auth-story-orbit-one" aria-hidden="true"></div>
            <div class="auth-story-orbit auth-story-orbit-two" aria-hidden="true"></div>

            <div class="auth-story-content">
                <div class="auth-story-heading">
                    <span class="auth-story-kicker">
                        <span class="size-2 rounded-full bg-primary-500"></span>
                        Clareza que acompanha sua rotina
                    </span>
                    <h1>Seu dinheiro faz mais sentido quando você vê o todo.</h1>
                    <p>Transforme movimentações soltas em decisões objetivas para hoje e para os próximos meses.</p>
                </div>

                <div class="auth-insight-card">
                    <div class="auth-insight-topline">
                        <span><i class="fa-solid fa-sparkles" aria-hidden="true"></i> Visão financi.ai</span>
                        <span>Este mês</span>
                    </div>

                    <p class="auth-insight-copy">Mantendo o ritmo atual, você encerra o mês com saldo positivo.</p>

                    <div class="auth-insight-balance">
                        <div>
                            <span>Saldo projetado</span>
                            <strong>R$ 3.570,00</strong>
                        </div>
                        <span class="auth-positive-pill"><i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i> 12,4%</span>
                    </div>

                    <div class="auth-mini-chart" aria-hidden="true">
                        <span style="height: 28%"></span>
                        <span style="height: 38%"></span>
                        <span style="height: 34%"></span>
                        <span style="height: 52%"></span>
                        <span style="height: 60%"></span>
                        <span style="height: 73%"></span>
                        <span style="height: 82%"></span>
                        <span style="height: 100%"></span>
                    </div>

                    <div class="auth-insight-metrics">
                        <div><span>Receitas</span><strong>R$ 8.920</strong></div>
                        <div><span>Despesas</span><strong>R$ 5.350</strong></div>
                        <div><span>Próximo objetivo</span><strong>Reserva</strong></div>
                    </div>
                </div>

                <div class="auth-story-footer">
                    <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Privado por padrão</span>
                    <span>Planeje. Acompanhe. Decida.</span>
                </div>
            </div>
        </aside>
    </main>
</body>
</html>
