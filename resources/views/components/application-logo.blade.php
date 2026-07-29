@props([
    'compact' => false,
    'variant' => null,
    'size' => 'default',
])

@php
    $resolvedVariant = $variant ?? ($compact ? 'symbol' : 'wordmark');
    $symbolClass = match (true) {
        $resolvedVariant === 'symbol' && $size === 'large' => 'size-16',
        default => 'size-10',
    };
    $wordmarkClass = match ($size) {
        'auth' => 'brand-wordmark text-4xl',
        'sidebar' => 'brand-wordmark text-xl',
        default => 'brand-wordmark text-2xl',
    };
@endphp

<span
    role="img"
    aria-label="{{ config('app.name') }}"
    data-logo-variant="{{ $resolvedVariant }}"
    {{ $attributes->class(['inline-flex shrink-0 overflow-hidden']) }}
>
    @if(in_array($resolvedVariant, ['wordmark', 'wordmark-white'], true))
        <span
            class="{{ $wordmarkClass }} {{ $resolvedVariant === 'wordmark-white' ? 'brand-wordmark-inverse' : '' }}"
            aria-hidden="true"
        >financi<span>.ai</span></span>
    @elseif($resolvedVariant === 'stacked')
        <span class="inline-flex flex-col items-center gap-1.5" aria-hidden="true">
            <img
                src="{{ asset('images/brand/financi-ai-symbol.svg') }}"
                alt=""
                class="size-14 shrink-0"
                width="56"
                height="56"
            >
            <span class="brand-wordmark text-xl">financi<span>.ai</span></span>
        </span>
    @else
        <img
            src="{{ asset('images/brand/financi-ai-symbol.svg') }}"
            alt=""
            class="{{ $symbolClass }} shrink-0"
            width="{{ $size === 'large' ? 64 : 40 }}"
            height="{{ $size === 'large' ? 64 : 40 }}"
            aria-hidden="true"
        >
    @endif
</span>
