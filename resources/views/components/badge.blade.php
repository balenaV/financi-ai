@props(['tone' => 'neutral'])
@php
$classes = match($tone) {
    'success', 'completed', 'paid', 'active', 'income' => 'bg-success-50 text-success-700 ring-success-100',
    'danger', 'overdue', 'cancelled', 'expense' => 'bg-danger-50 text-danger-700 ring-danger-100',
    'warning', 'planned', 'pending' => 'bg-warning-50 text-warning-700 ring-warning-100',
    'primary', 'transfer' => 'bg-primary-50 text-primary-800 ring-primary-200',
    default => 'bg-slate-100 text-slate-700 ring-slate-200',
};
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {$classes}"]) }}>{{ $slot }}</span>
