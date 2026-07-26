<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f1f3d">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/financi-ai-symbol.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <script>
        try {
            if (localStorage.getItem('financi-theme') === 'dark') document.documentElement.classList.add('dark');
        } catch (error) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-primary-900">
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_.95fr]">
        <section class="hidden overflow-hidden p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <x-application-logo variant="symbol" size="large" />
            <div class="max-w-xl">
                <p class="text-sm font-semibold uppercase tracking-[.22em] text-primary-200">Sua vida financeira, clara</p>
                <h1 class="mt-5 text-5xl font-bold leading-tight tracking-tight">Decisões melhores começam com números organizados.</h1>
                <p class="mt-6 text-lg leading-relaxed text-primary-200">Contas, dívidas, investimentos, metas e orçamento em uma visão segura e objetiva.</p>
            </div>
            <p class="text-sm text-primary-200">Privado por padrão. Seus dados ficam sob controle do Laravel.</p>
        </section>
        <section class="flex items-center justify-center bg-slate-50 p-4 sm:p-8 lg:rounded-l-[2rem]">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden"><x-application-logo /></div>
                <div class="mb-4 hidden justify-center lg:flex"><x-application-logo variant="stacked" size="auth" /></div>
                <div class="surface p-6 sm:p-8">{{ $slot }}</div>
            </div>
        </section>
    </main>
</body>
</html>
