@props(['label', 'value', 'tone' => 'neutral', 'hint' => null])
@php
$colors = match($tone) {
    'positive' => 'text-accent-800',
    'negative' => 'text-red-700',
    'primary' => 'text-primary-800',
    default => 'text-slate-900',
};
@endphp
<article {{ $attributes->merge(['class' => 'surface p-5']) }}>
    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold tracking-tight {{ $colors }}"><x-money :value="$value" /></p>
    @if($hint)<p class="mt-2 text-xs text-slate-500">{{ $hint }}</p>@endif
</article>
