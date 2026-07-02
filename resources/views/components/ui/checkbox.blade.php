@props([
    'label',
    'name',
    'checked' => false,
])

       
<label class="handoff-checkbox">
    <input type="checkbox" name="{{ $name }}" value="1" @checked($checked)
        {{ $attributes->merge(['class' => 'peer sr-only']) }} />
    <span class="handoff-checkbox-box"></span>
    <span class="handoff-checkbox-label">{{ $label }}</span>
</label>
