<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ request()->routeIs('login') ? 'auth-login-root' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FFF6E6">
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
                <div class="auth-header-row">
                    <a href="{{ url('/') }}" class="auth-brand" aria-label="Página inicial">
                        <img src="{{ asset('images/mascot/capi-rosto.png') }}" alt="" class="size-8 shrink-0 object-contain">
                        <span class="auth-brand-wordmark">financi<span>aí</span></span>
                    </a>
                    <div class="auth-header-actions">
                        <button type="button" data-landing-theme class="app-icon-button !size-9" aria-label="Alternar tema" title="Alternar tema">
                            <i class="fa-solid fa-moon dark:hidden" aria-hidden="true"></i>
                            <i class="fa-solid fa-sun hidden dark:inline" aria-hidden="true"></i>
                        </button>
                        <a href="{{ url('/') }}" class="btn-secondary !min-h-9 !px-3.5 !text-sm">Voltar ao site</a>
                    </div>
                </div>

                <div class="auth-form-card">
                    <div>{{ $slot }}</div>
                </div>

                <p class="auth-security-note">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                    Seus dados são protegidos e nunca são compartilhados.
                </p>

                <div class="auth-footer-row">
                    <span>© {{ now()->year }} financiaí</span>
                    <a href="#">Privacidade</a>
                    <a href="#">Termos</a>
                    <a href="#">Ajuda</a>
                </div>
            </div>
        </section>

        <aside class="auth-story" aria-label="Visão do produto">
            <div class="absolute rounded-full" style="top:-90px; right:-70px; width:260px; height:260px; border:30px solid rgba(19,122,74,0.5);" aria-hidden="true"></div>
            <div class="absolute rounded-full" style="bottom:12%; left:8%; width:6px; height:6px; background:rgba(56,193,114,0.6);" aria-hidden="true"></div>
            <div class="absolute rounded-full" style="top:22%; left:22%; width:4px; height:4px; background:rgba(255,246,230,0.4);" aria-hidden="true"></div>

            <div class="auth-story-content">
                <div class="auth-story-heading">
                    <span class="auth-story-kicker">
                        <span class="size-1.5 rounded-full bg-[#38C172]"></span>
                        Beta aberto
                    </span>
                    <h1>Sua vida financeira inteira em um só painel.</h1>
                    <p>Contas, cartões, metas e compromissos futuros organizados para você decidir com clareza — hoje e nos próximos doze meses.</p>
                </div>

                <ul class="auth-proof-list">
                    <li class="auth-proof-item"><i class="fa-solid fa-check" aria-hidden="true"></i>Contas, cartões e faturas no mesmo painel</li>
                    <li class="auth-proof-item"><i class="fa-solid fa-check" aria-hidden="true"></i>Receitas e despesas futuras já projetadas</li>
                    <li class="auth-proof-item"><i class="fa-solid fa-check" aria-hidden="true"></i>Dados isolados por usuário e histórico de segurança</li>
                    <li class="auth-proof-item"><i class="fa-solid fa-check" aria-hidden="true"></i>Exportação dos seus dados quando quiser</li>
                </ul>

                <div class="flex flex-wrap items-end gap-5">
                    <img src="{{ asset('images/mascot/capi-comemorando.png') }}" alt="" class="w-40 shrink-0 sm:w-52">
                    <div class="auth-story-stats flex-1" style="min-width: 11rem;">
                        <div><strong>12 meses</strong><span>de visão futura</span></div>
                        <div><strong>100%</strong><span>dados isolados</span></div>
                    </div>
                </div>
            </div>
        </aside>
    </main>
</body>
</html>
