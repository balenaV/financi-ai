@props(['name', 'options', 'selected' => null, 'icon' => null, 'up' => false, 'block' => true])
@php
    $options = collect($options)->values();
    $selectedOption = $options->first(fn ($o) => (string) $o['value'] === (string) $selected) ?? $options->first();
@endphp
<div class="dropdown {{ $block ? 'dropdown--block' : '' }} {{ $up ? 'dropdown--up' : '' }}" data-dropdown>
  <button class="dropdown__btn" type="button" aria-haspopup="listbox" aria-expanded="false" data-dropdown-btn>
    @if($icon)<i class="{{ $icon }}"></i>@endif
    <span data-dropdown-label>{{ $selectedOption['label'] ?? 'Selecione' }}</span>
    <i class="fa-solid fa-chevron-down dropdown__chevron"></i>
  </button>
  <div class="dropdown__menu" role="listbox" hidden>
    @foreach($options as $option)
      <button class="dropdown__opt {{ $selectedOption && (string) $option['value'] === (string) $selectedOption['value'] ? 'is-selected' : '' }}" type="button" role="option" data-value="{{ $option['value'] }}">{{ $option['label'] }}<i class="fa-solid fa-check"></i></button>
    @endforeach
  </div>
  <input type="hidden" name="{{ $name }}" value="{{ $selectedOption['value'] ?? '' }}" data-dropdown-input {{ $attributes }}>
</div>
