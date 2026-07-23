@props(['value' => '0.00', 'class' => ''])
@php
    $hide = auth()->user()?->settings?->hide_values ?? false;
    $formatted = \App\Support\Money::format((string) $value);
@endphp
<span {{ $attributes->merge(['class' => 'money-value '.$class]) }}
      data-visible-value="{{ $formatted }}"
      data-hidden-value="R$ ••••••">{{ $hide ? 'R$ ••••••' : $formatted }}</span>
