@props([
    'icon',
    'label',
])

<div class="handoff-feature-wrap">
    <div class="handoff-feature-frame">
        <div class="handoff-feature-shell">
            <span
                class="handoff-clip flex size-12 items-center justify-center bg-brand-700 text-white dark:bg-brand-600">
                <x-dynamic-component :component="'flux::icon.' . $icon" variant="mini" class="size-6 text-white" />
            </span>
            <flux:text class="text-sm font-semibold text-brand-900 dark:text-brand-100">{{ $label }}</flux:text>
        </div>
    </div>
</div>
