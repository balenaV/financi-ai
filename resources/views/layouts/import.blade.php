<!DOCTYPE html>
<html lang="pt-BR" data-theme="{{ auth()->user()->settings()->firstOrCreate()->theme }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? 'Importar extrato' }} — financiaí</title>
<link rel="icon" type="image/png" href="{{ asset('design/assets/capi/capi-rosto.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('design/css/tokens.css') }}">
<link rel="stylesheet" href="{{ asset('design/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('design/css/importar.css') }}">
{{-- dashboard.css só entra pelo componente .dropdown (menu de sistema) —
     importar.css nunca teve esse componente, e trocar os <select> nativos
     por ele deixa a importação com a mesma cara do resto do painel. Sem
     conflito de nomes confirmado: nenhuma classe de dashboard.css colide
     com as classes próprias de importar.css (.card, .note, .btn-step-*...). --}}
<link rel="stylesheet" href="{{ asset('design/css/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('design/css/app-additions.css') }}">
</head>
<body>

{{ $slot }}

<script src="{{ asset('design/js/theme.js') }}"></script>
{{-- dashboard.js só é usado aqui pela lógica genérica de abrir/fechar
     .dropdown e marcar .is-selected — o resto do arquivo (abas, modais,
     período) não encontra os elementos que espera nesta página e não faz nada. --}}
<script src="{{ asset('design/js/dashboard.js') }}"></script>
<script src="{{ asset('design/js/dashboard-settings-sync.js') }}"></script>
{{-- form-widgets-sync.js copia o valor escolhido no .dropdown pro input
     oculto correspondente (dashboard.js só cuida da parte visual) — sem ele
     os dropdowns desta página abrem e marcam a opção, mas nunca atualizam
     o valor de verdade lido pelo import-wizard.js. --}}
<script src="{{ asset('design/js/form-widgets-sync.js') }}"></script>
<script src="{{ asset('design/js/import-wizard.js') }}"></script>
</body>
</html>
