@props([
    'icon',
    'label',
    'value' => '0',
])

<div class="handoff-stat-card">
    <div class="handoff-stat-card__icon">
        <x-dynamic-component :component="'flux::icon.' . $icon" variant="mini" class="size-5" />
    </div>
    <div>
        <p class="handoff-stat-card__value">{{ $value }}</p>
        <p class="handoff-stat-card__label">{{ $label }}</p>
    </div>
</div>
