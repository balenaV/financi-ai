@props(['value' => 0, 'tone' => 'primary'])
@php
    $width = min(100, max(0, (float) $value));
    $color = match($tone) {
        'danger' => 'bg-red-600',
        'warning', 'attention' => 'bg-amber-500',
        'success', 'ok' => 'bg-accent-600',
        default => 'bg-primary-600',
    };
@endphp
<div class="h-2.5 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-valuenow="{{ $width }}" aria-valuemin="0" aria-valuemax="100">
    <div class="h-full rounded-full {{ $color }}" style="width: {{ $width }}%"></div>
</div>
