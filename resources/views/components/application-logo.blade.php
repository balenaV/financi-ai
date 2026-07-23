@props([
    'compact' => false,
    'variant' => null,
    'size' => 'default',
])

@php
    $resolvedVariant = $variant ?? ($compact ? 'symbol' : 'wordmark');
    $brandAsset = match ($resolvedVariant) {
        'symbol' => 'images/brand/financi-ai-symbol.png',
        'stacked' => 'images/brand/financi-ai-stacked.png',
        'wordmark-white' => 'images/brand/financi-ai-wordmark-white.png',
        default => 'images/brand/financi-ai-wordmark.png',
    };
    $darkBrandAsset = $resolvedVariant === 'wordmark'
        ? 'images/brand/financi-ai-wordmark-white.png'
        : $brandAsset;
    $brandClass = match (true) {
        $resolvedVariant === 'wordmark' && $size === 'sidebar' => 'h-8 w-36',
        $resolvedVariant === 'symbol' && $size === 'large' => 'size-16',
        $resolvedVariant === 'symbol' => 'size-10',
        $resolvedVariant === 'stacked' && $size === 'auth' => 'h-24 w-36',
        $resolvedVariant === 'stacked' => 'h-40 w-56',
        default => 'h-9 w-48',
    };
    $brandSize = match ($resolvedVariant) {
        'symbol' => '180%',
        'stacked' => '135%',
        default => '123% auto',
    };
    $brandPosition = match ($resolvedVariant) {
        'symbol' => '61% 50%',
        'stacked' => '50% 48%',
        default => '50% 50%',
    };
@endphp

<span
    role="img"
    aria-label="{{ config('app.name') }}"
    data-logo-variant="{{ $resolvedVariant }}"
    {{ $attributes->class(['inline-flex shrink-0 overflow-hidden']) }}
>
    <span
        class="block shrink-0 bg-no-repeat {{ $brandClass }}"
        style="--logo-light-image: url('{{ asset($brandAsset) }}'); --logo-dark-image: url('{{ asset($darkBrandAsset) }}'); background-image: var(--logo-light-image); background-size: {{ $brandSize }}; background-position: {{ $brandPosition }};"
        aria-hidden="true"
    ></span>
</span>
