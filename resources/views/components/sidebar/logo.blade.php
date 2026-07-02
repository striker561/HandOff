@props([
    'href' => null,
])

<a href="{{ $href ?? route('dashboard') }}" wire:navigate {{ $attributes->class('handoff-sidebar__logo') }}>
    <x-ui.logo-mark size="lg" variant="sidebar" />
</a>
