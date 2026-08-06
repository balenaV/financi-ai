<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? 'financiaí — organize suas finanças hoje para viver melhor amanhã' }}</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/financi-ai-symbol.svg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('design/css/tokens.css') }}">
<link rel="stylesheet" href="{{ asset('design/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('design/css/landing.css') }}">
<script>
    (function () {
        try {
            var theme = localStorage.getItem('financiai:theme');
            if (theme === 'dark' || theme === 'light') {
                document.documentElement.setAttribute('data-theme', theme);
            }
        } catch (e) {}
    })();
</script>
</head>
<body class="landing">

{{ $slot }}

<script src="{{ asset('design/js/theme.js') }}"></script>
<script src="{{ asset('design/js/landing.js') }}"></script>
</body>
</html>
