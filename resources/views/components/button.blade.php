@props(['variant' => 'primary', 'type' => 'button'])
@php
    $classes = match($variant) {
        'secondary' => 'btn-secondary',
        'danger' => 'btn-danger',
        'ghost' => 'btn-ghost',
        'destaque', 'highlight' => 'btn-destaque',
        default => 'btn-primary',
    };
@endphp
<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
