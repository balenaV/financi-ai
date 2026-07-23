@props(['tone' => 'neutral'])
@php
$classes = match($tone) {
    'success', 'completed', 'paid', 'active', 'income' => 'bg-accent-50 text-accent-800 ring-accent-400/30',
    'danger', 'overdue', 'cancelled', 'expense' => 'bg-red-50 text-red-700 ring-red-200',
    'warning', 'planned', 'pending' => 'bg-amber-50 text-amber-800 ring-amber-200',
    'primary', 'transfer' => 'bg-primary-50 text-primary-800 ring-primary-200',
    default => 'bg-slate-100 text-slate-700 ring-slate-200',
};
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {$classes}"]) }}>{{ $slot }}</span>
