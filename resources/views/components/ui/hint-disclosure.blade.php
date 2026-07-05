@props([
    'heading',
    'icon' => 'information-circle',
])

<div x-data="{ open: false }" {{ $attributes->class(['handoff-hint-disclosure']) }}>
    <button type="button" @click="open = !open" class="handoff-hint-disclosure__trigger">
        <x-dynamic-component :component="'flux::icon.' . $icon" variant="mini" class="size-4 shrink-0" />
        <span>{{ $heading }}</span>
        <flux:icon.chevron-down variant="mini" class="ml-auto size-4 shrink-0 transition"
            x-bind:class="open && 'rotate-180'" />
    </button>

    <div x-show="open" x-cloak x-transition class="handoff-hint-disclosure__body">
        {{ $slot }}
    </div>
</div>
