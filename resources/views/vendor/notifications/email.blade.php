<x-mail::message>
{{-- Saudação --}}
@if (! empty($greeting))
# {{ $greeting }}
@elseif ($level === 'error')
# Algo precisa da sua atenção
@else
# Olá!
@endif

{{-- Linhas introdutórias --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Ação principal --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Linhas finais --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Assinatura --}}
@if (! empty($salutation))
{{ $salutation }}
@else
Até já,<br>
Equipe {{ config('app.name') }}
@endif

{{-- Alternativa ao botão --}}
@isset($actionText)
<x-slot:subcopy>
Se o botão “{{ $actionText }}” não funcionar, copie e cole este endereço no navegador:
<span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
