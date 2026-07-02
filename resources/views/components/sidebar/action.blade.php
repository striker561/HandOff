@props([
    'href' => null,
    'icon',
    'label',
    'current' => false,
])

@if ($href)
    <a href="{{ $href }}" wire:navigate
        @class([
            'handoff-sidebar-action',
            'handoff-sidebar-action--current' => $current,
        ])
        @if ($current) aria-current="page" @endif>
        <x-dynamic-component :component="'flux::icon.' . $icon" variant="mini" class="size-4" />
        <span>{{ $label }}</span>
    </a>
@else
    <button type="button"
        {{ $attributes->class([
            'handoff-sidebar-action',
            'handoff-sidebar-action--current' => $current,
        ]) }}>
        <x-dynamic-component :component="'flux::icon.' . $icon" variant="mini" class="size-4" />
        <span>{{ $label }}</span>
    </button>
@endif
