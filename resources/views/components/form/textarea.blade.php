@props(['label', 'name', 'value' => null, 'required' => false])
<label class="block">
    <span class="form-label">{{ $label }} @if($required)<span class="text-red-600">*</span>@endif</span>
    <textarea name="{{ $name }}" rows="3" @required($required)
        @if($errors->has($name)) aria-invalid="true" @endif
        {{ $attributes->merge(['class' => 'form-control'.($errors->has($name) ? ' border-danger-500' : '')]) }}>{{ old($name, $value) }}</textarea>
    @error($name)<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
</label>
