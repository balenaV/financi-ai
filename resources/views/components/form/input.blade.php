@props(['label', 'name', 'type' => 'text', 'value' => null, 'required' => false, 'withToggle' => true])
@php($isPassword = $type === 'password' && $withToggle)
<label class="block">
    <span class="flex items-center justify-between gap-3">
        <span class="form-label">{{ $label }} @if($required)<span class="text-red-600">*</span>@endif</span>
        {{ $labelEnd ?? '' }}
    </span>
    @if($isPassword)
        <span class="form-control flex items-center gap-2 !py-0 pr-2">
            <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}" @required($required)
                @if($errors->has($name)) aria-invalid="true" @endif
                {{ $attributes->merge(['class' => 'min-w-0 flex-1 border-0 bg-transparent p-0 text-sm outline-none']) }}>
            <button type="button" data-toggle-password aria-label="Mostrar ou ocultar senha" class="shrink-0 text-xs font-bold text-foreground-tertiary hover:text-foreground">Mostrar</button>
        </span>
    @else
        <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}" @required($required)
            @if($errors->has($name)) aria-invalid="true" @endif
            {{ $attributes->merge(['class' => 'form-control'.($errors->has($name) ? ' border-danger-500' : '')]) }}>
    @endif
    @error($name)<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
</label>
