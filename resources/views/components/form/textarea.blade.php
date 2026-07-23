@props(['label', 'name', 'value' => null])
<label class="block">
    <span class="form-label">{{ $label }}</span>
    <textarea name="{{ $name }}" rows="3" {{ $attributes->merge(['class' => 'form-control']) }}>{{ old($name, $value) }}</textarea>
    @error($name)<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
</label>
