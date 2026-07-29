<x-mail::layout>
{{-- Marca --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
<span class="brand-wordmark">financi<span class="brand-ai">.ai</span></span>
<span class="brand-tagline">clareza para suas finanças</span>
</x-mail::header>
</x-slot:header>

{{-- Conteúdo --}}
{!! $slot !!}

{{-- Link alternativo --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Rodapé --}}
<x-slot:footer>
<x-mail::footer>
Organize hoje. Decida melhor amanhã.<br>
© {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
