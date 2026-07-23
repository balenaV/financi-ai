@props(['variant' => 'primary', 'type' => 'button'])
@php
    $classes = match($variant) {
        'secondary' => 'btn-secondary',
        'danger' => 'btn-danger',
        'ghost' => 'btn text-slate-600 hover:bg-slate-100',
        default => 'btn-primary',
    };
@endphp
<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
