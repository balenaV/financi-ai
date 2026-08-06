@props(['name', 'value', 'label' => null, 'inline' => false, 'right' => false, 'up' => false, 'bare' => false])
@php
    $display = $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y') : '';
@endphp
<div class="datepicker {{ $inline ? 'datepicker--inline' : 'datepicker--block' }} {{ $right ? 'datepicker--right' : '' }} {{ $up ? 'datepicker--up' : '' }}" data-datepicker data-value="{{ $value }}">
  <button class="{{ $bare ? 'datepicker__btn datepicker__btn--bare' : 'dropdown__btn datepicker__btn' }}" type="button" aria-haspopup="dialog" aria-expanded="false" data-datepicker-btn @if($label) aria-label="{{ $label }}" @endif>
    @unless($bare)<i class="fa-regular fa-calendar"></i>@endunless
    <span data-datepicker-label>{{ $display }}</span>
    @unless($bare)<i class="fa-solid fa-chevron-down dropdown__chevron"></i>@endunless
  </button>
  <div class="datepicker__panel" hidden data-datepicker-panel></div>
</div>
<input type="hidden" name="{{ $name }}" value="{{ $value }}" {{ $attributes }}>
