@props(['label', 'name', 'required' => false])
<label class="block">
    <span class="form-label">{{ $label }} @if($required)<span class="text-red-600">*</span>@endif</span>
    <select name="{{ $name }}" @required($required)
        {{ $attributes->merge(['class' => 'form-control'.($errors->has($name) ? ' border-red-500' : '')]) }}>
        {{ $slot }}
    </select>
    @error($name)<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
</label>
