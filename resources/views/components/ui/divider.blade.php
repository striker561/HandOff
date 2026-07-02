@props([
    'label',
])

<div {{ $attributes->merge(['class' => 'handoff-divider']) }} role="separator">
    <span>{{ $label }}</span>
</div>
