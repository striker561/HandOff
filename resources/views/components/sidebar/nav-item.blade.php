@props([
    'href',
    'icon',
    'label',
    'current' => false,
    'badge' => null,
])

<a href="{{ $href }}" wire:navigate
    @class([
        'handoff-sidebar-link',
        'handoff-sidebar-link--current' => $current,
    ])
    @if ($current) aria-current="page" @endif>
    <span class="handoff-sidebar-link__icon" aria-hidden="true">
        <x-dynamic-component :component="'flux::icon.' . $icon" variant="mini" class="size-[1.125rem]" />
    </span>
    <span class="handoff-sidebar-link__label">{{ $label }}</span>
    @if ($badge)
        <span class="handoff-sidebar-link__badge">{{ $badge }}</span>
    @endif
</a>
