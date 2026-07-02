@props([
    'href' => null,
])

<a href="{{ $href ?? route('dashboard') }}" wire:navigate {{ $attributes->class('handoff-sidebar__logo') }}>
    <span class="handoff-sidebar__logo-mark">
        <x-app-logo-icon class="size-6 text-white" />
    </span>
</a>
