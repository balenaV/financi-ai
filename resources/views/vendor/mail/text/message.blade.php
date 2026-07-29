<x-mail::layout>
<x-slot:header>
financi.ai — clareza para suas finanças
</x-slot:header>

{{ $slot }}

@isset($subcopy)
<x-slot:subcopy>
{{ $subcopy }}
</x-slot:subcopy>
@endisset

<x-slot:footer>
Organize hoje. Decida melhor amanhã.
© {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
</x-slot:footer>
</x-mail::layout>
